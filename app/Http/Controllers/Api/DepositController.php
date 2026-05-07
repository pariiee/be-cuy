<?php

namespace App\Http\Controllers\Api;

use App\Models\Deposit;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DepositController extends BaseApiController
{
    public function __construct(
        protected PaymentService $payment
    ) {}

    /**
     * POST /api/deposits
     *
     * Create a new deposit (top up saldo) via QRIS.
     * Body: { "amount": 50000 }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100|max:2000000',
        ]);

        if (!$this->payment->isQrisEnabled()) {
            return $this->error('Pembayaran QRIS sedang tidak tersedia', 422);
        }

        $user = $request->user();
        $amount = (int) $validated['amount'];

        // Calculate our markup fee for deposits
        $markupFee = $this->payment->calculateQrisFee($amount, 'deposit', $user);
        $totalAmount = $amount + (int) ceil($markupFee);

        $deposit = $this->payment->createQrisForDeposit($user->id, $totalAmount, $user->name);

        if ($deposit === null) {
            return $this->error('Gagal membuat QRIS. Coba lagi nanti.', 502);
        }

        return $this->success([
            'deposit_id' => $deposit->id,
            'invoice_number' => $deposit->invoice_number,
            'amount' => $amount,
            'markup_fee' => $markupFee,
            'total_amount' => $totalAmount,
            'qris_content' => $deposit->qris_content,
            'qris_image_url' => $deposit->qris_image_url,
            'payinaja_trx_id' => $deposit->payinaja_trx_id,
            'payinaja_fee' => $deposit->payinaja_fee,
            'payinaja_total' => $deposit->payinaja_total,
            'expired_at' => $deposit->qris_expired_at->toIso8601String(),
        ], 'QRIS berhasil dibuat. Scan dan bayar dalam 15 menit.', 201);
    }

    /**
     * GET /api/deposits/{deposit}/check
     *
     * Check payment status of a deposit/order-payment.
     * Works for both deposit (top up saldo) and order payment QRIS.
     */
    public function checkStatus(Request $request, Deposit $deposit)
    {
        $user = $request->user();

        if ($deposit->user_id !== $user->id) {
            return $this->error('Deposit tidak ditemukan', 404);
        }

        $result = $this->payment->checkAndProcessQris($deposit);

        if (($result['status'] ?? '') === 'error') {
            return $this->error($result['message'] ?? 'Gagal cek status', 502);
        }

        if ($result['status'] === 'paid') {
            $data = [
                'deposit_id' => $deposit->id,
                'status' => 'paid',
                'purpose' => $deposit->purpose,
                'amount' => $deposit->amount,
                'payment_by' => $result['payment_by'] ?? null,
                'customer_name' => $result['customer_name'] ?? null,
            ];

            if ($deposit->purpose === 'deposit') {
                $data['new_balance'] = $user->fresh()->balance;
                $msg = 'Pembayaran berhasil! Saldo telah ditambahkan.';
            } else {
                $data['order_id'] = $deposit->order_id;
                $data['order_status'] = $deposit->order?->fresh()->status;
                $msg = 'Pembayaran berhasil! Order sedang diproses.';
            }

            return $this->success($data, $msg);
        }

        if ($result['status'] === 'expired') {
            return $this->success([
                'deposit_id' => $deposit->id,
                'status' => 'expired',
                'purpose' => $deposit->purpose,
                'amount' => $deposit->amount,
            ], 'QRIS sudah expired.');
        }

        return $this->success([
            'deposit_id' => $deposit->id,
            'status' => 'unpaid',
            'purpose' => $deposit->purpose,
            'amount' => $deposit->amount,
            'expired_at' => $deposit->qris_expired_at?->toIso8601String(),
        ], 'Belum dibayar. Silakan scan QRIS.');
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
     * Get deposit detail (including QRIS content for re-display).
     */
    public function show(Request $request, Deposit $deposit)
    {
        if ($deposit->user_id !== $request->user()->id) {
            return $this->error('Deposit tidak ditemukan', 404);
        }

        return $this->success([
            'deposit_id' => $deposit->id,
            'invoice_number' => $deposit->invoice_number,
            'amount' => $deposit->amount,
            'purpose' => $deposit->purpose,
            'status' => $deposit->status,
            'order_id' => $deposit->order_id,
            'qris_content' => $deposit->status === 'pending' ? $deposit->qris_content : null,
            'qris_image_url' => $deposit->status === 'pending' ? $deposit->qris_image_url : null,
            'payinaja_trx_id' => $deposit->payinaja_trx_id,
            'payinaja_fee' => $deposit->payinaja_fee,
            'payinaja_total' => $deposit->payinaja_total,
            'expired_at' => $deposit->qris_expired_at?->toIso8601String(),
            'payment_by' => $deposit->payment_method_by,
            'customer_name' => $deposit->payment_customer_name,
            'paid_at' => $deposit->paid_at?->toIso8601String(),
            'created_at' => $deposit->created_at->toIso8601String(),
        ]);
    }
}
