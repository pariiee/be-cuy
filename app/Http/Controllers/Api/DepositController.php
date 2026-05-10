<?php

namespace App\Http\Controllers\Api;

use App\Models\Deposit;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DepositController extends BaseApiController
{
    public function __construct(
        protected MidtransService $midtrans,
        protected PaymentService $payment
    ) {}

    /**
     * POST /api/deposits
     *
     * Create a new deposit (top up saldo) via Midtrans Snap.
     * Body: { "amount": 50000 }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1000|max:10000000',
        ]);

        if (!$this->midtrans->isEnabled()) {
            return $this->error('Pembayaran Midtrans belum dikonfigurasi.', 503);
        }

        $user      = $request->user();
        $amount    = (int) $validated['amount'];
        $invoiceNo = 'MID-DEP-' . $user->id . '-' . time();

        $snap = $this->midtrans->createSnapToken([
            'order_id'     => $invoiceNo,
            'gross_amount' => $amount,
            'name'         => 'Deposit Saldo Rp ' . number_format($amount, 0, ',', '.'),
            'customer'     => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
            'finish_url'   => config('app.url') . '/deposit/finish',
        ]);

        if (!$snap) {
            return $this->error('Gagal membuat pembayaran Midtrans. Coba lagi nanti.', 502);
        }

        $deposit = Deposit::create([
            'user_id'               => $user->id,
            'invoice_number'        => $invoiceNo,
            'amount'                => $amount,
            'method'                => 'midtrans',
            'purpose'               => 'deposit',
            'status'                => 'pending',
            'midtrans_snap_token'   => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
        ]);

        return $this->success([
            'deposit_id'   => $deposit->id,
            'invoice_no'   => $invoiceNo,
            'amount'       => $amount,
            'snap_token'   => $snap['snap_token'],
            'redirect_url' => $snap['redirect_url'],
            'client_key'   => config('services.midtrans.client_key'),
        ], 'Snap token berhasil dibuat. Selesaikan pembayaran via Midtrans.', 201);
    }

    /**
     * GET /api/deposits/{deposit}/check
     *
     * Check & sync Midtrans payment status for a deposit.
     */
    public function checkStatus(Request $request, Deposit $deposit)
    {
        $user = $request->user();

        if ($deposit->user_id !== $user->id) {
            return $this->error('Deposit tidak ditemukan', 404);
        }

        if (in_array($deposit->status, ['paid', 'failed'])) {
            $data = [
                'deposit_id' => $deposit->id,
                'status'     => $deposit->status,
                'purpose'    => $deposit->purpose,
                'amount'     => $deposit->amount,
                'paid_at'    => $deposit->paid_at?->toIso8601String(),
            ];
            if ($deposit->purpose === 'deposit') {
                $data['new_balance'] = $user->fresh()->balance;
            } else {
                $data['order_id']     = $deposit->order_id;
                $data['order_status'] = $deposit->order?->status;
            }
            return $this->success($data, $deposit->status === 'paid' ? 'Pembayaran berhasil.' : 'Pembayaran gagal/dibatalkan.');
        }

        // Pull live status from Midtrans
        $live = $this->midtrans->checkStatus($deposit->invoice_number);

        if ($live) {
            $newStatus = $this->midtrans->mapStatus(
                $live['transaction_status'] ?? 'pending',
                $live['fraud_status'] ?? ''
            );

            if ($newStatus !== $deposit->status) {
                $deposit->update([
                    'status'                  => $newStatus,
                    'midtrans_transaction_id' => $live['transaction_id'] ?? $deposit->midtrans_transaction_id,
                    'midtrans_payment_type'   => $live['payment_type'] ?? $deposit->midtrans_payment_type,
                    'paid_at'                 => $newStatus === 'paid' ? now() : $deposit->paid_at,
                    'payment_method_by'       => $live['payment_type'] ?? $deposit->payment_method_by,
                ]);

                if ($newStatus === 'paid') {
                    if ($deposit->purpose === 'deposit') {
                        User::lockForUpdate()->find($deposit->user_id)?->increment('balance', (float) $deposit->amount);
                    } elseif ($deposit->purpose === 'order_payment' && $deposit->order_id) {
                        $deposit->order?->update(['payment_status' => 'lunas']);
                        $this->payment->fulfillOrder($deposit->order);
                    }
                }
            }
        }

        $fresh = $deposit->fresh();

        $data = [
            'deposit_id'   => $fresh->id,
            'status'       => $fresh->status,
            'purpose'      => $fresh->purpose,
            'amount'       => $fresh->amount,
            'payment_type' => $fresh->midtrans_payment_type,
            'paid_at'      => $fresh->paid_at?->toIso8601String(),
        ];

        if ($fresh->purpose === 'deposit' && $fresh->status === 'paid') {
            $data['new_balance'] = $user->fresh()->balance;
        } elseif ($fresh->order_id) {
            $data['order_id']     = $fresh->order_id;
            $data['order_status'] = $fresh->order?->status;
        }

        $msg = match ($fresh->status) {
            'paid'    => 'Pembayaran berhasil!',
            'failed'  => 'Pembayaran gagal/dibatalkan.',
            default   => 'Menunggu pembayaran.',
        };

        return $this->success($data, $msg);
    }

    /**
     * GET /api/deposits
     *
     * Get user's deposit/payment history.
     * Query: ?purpose=deposit or ?purpose=order_payment
     */
    public function index(Request $request)
    {
        $query = $request->user()->deposits()->orderByDesc('created_at');

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->query('purpose'));
        }

        return $this->success($query->paginate(20));
    }

    /**
     * GET /api/deposits/{deposit}
     *
     * Get deposit detail.
     */
    public function show(Request $request, Deposit $deposit)
    {
        if ($deposit->user_id !== $request->user()->id) {
            return $this->error('Deposit tidak ditemukan', 404);
        }

        return $this->success([
            'deposit_id'         => $deposit->id,
            'invoice_number'     => $deposit->invoice_number,
            'amount'             => $deposit->amount,
            'purpose'            => $deposit->purpose,
            'method'             => $deposit->method,
            'status'             => $deposit->status,
            'order_id'           => $deposit->order_id,
            'snap_token'         => $deposit->status === 'pending' ? $deposit->midtrans_snap_token : null,
            'redirect_url'       => $deposit->status === 'pending' ? $deposit->midtrans_redirect_url : null,
            'payment_type'       => $deposit->midtrans_payment_type,
            'transaction_id'     => $deposit->midtrans_transaction_id,
            'payment_by'         => $deposit->payment_method_by,
            'paid_at'            => $deposit->paid_at?->toIso8601String(),
            'created_at'         => $deposit->created_at->toIso8601String(),
        ]);
    }
}
