<?php

namespace App\Http\Controllers\Api;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransController extends BaseApiController
{
    public function __construct(
        protected MidtransService $midtrans,
        protected PaymentService $payment
    ) {}

    /**
     * POST /api/midtrans/snap
     *
     * Create a Midtrans Snap token.
     *
     * Purpose "deposit":
     *   Body: { "purpose": "deposit", "amount": 50000 }
     *   → Top up user balance after payment confirmed.
     *
     * Purpose "order":
     *   Body: { "purpose": "order", "order_id": 1 }
     *   → Pay for an existing pending order.
     *
     * Purpose "transaction":
     *   Body: { "purpose": "transaction", ...transaction fields... }
     *   → Create order + pay via Midtrans in one step.
     *   (Same fields as POST /api/transactions)
     */
    public function snap(Request $request)
    {
        if (!$this->midtrans->isEnabled()) {
            return $this->error('Midtrans belum dikonfigurasi.', 503);
        }

        $validated = $request->validate([
            'purpose'        => 'required|in:deposit,order,transaction',
            // deposit
            'amount'         => 'required_if:purpose,deposit|integer|min:1000|max:10000000',
            // order
            'order_id'       => 'required_if:purpose,order|integer',
            // transaction (new)
            'product_code'   => 'required_if:purpose,transaction|string|max:20',
            'destination'    => 'required_if:purpose,transaction|string|regex:/^[0-9]{6,20}$/',
            'product_name'   => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'base_price'     => 'required_if:purpose,transaction|numeric|min:0',
            // optional payment restriction
            'enabled_payments' => 'nullable|array',
        ]);

        $user = $request->user();

        return match ($validated['purpose']) {
            'deposit'     => $this->handleDeposit($user, $validated),
            'order'       => $this->handleOrderPayment($user, $validated),
            'transaction' => $this->handleNewTransaction($user, $validated),
        };
    }

    // ── Deposit ─────────────────────────────────────────────────

    private function handleDeposit(User $user, array $v)
    {
        $amount    = (int) $v['amount'];
        $invoiceNo = 'MID-DEP-' . $user->id . '-' . time();

        $snap = $this->midtrans->createSnapToken([
            'order_id'         => $invoiceNo,
            'gross_amount'     => $amount,
            'name'             => 'Deposit Saldo Rp ' . number_format($amount, 0, ',', '.'),
            'customer'         => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
            'enabled_payments' => $v['enabled_payments'] ?? null,
            'finish_url'       => config('app.url') . '/deposit/finish',
        ]);

        if (!$snap) {
            return $this->error('Gagal membuat Snap token. Coba lagi nanti.', 502);
        }

        $deposit = Deposit::create([
            'user_id'              => $user->id,
            'invoice_number'       => $invoiceNo,
            'amount'               => $amount,
            'method'               => 'midtrans',
            'purpose'              => 'deposit',
            'status'               => 'pending',
            'midtrans_snap_token'  => $snap['snap_token'],
            'midtrans_redirect_url'=> $snap['redirect_url'],
        ]);

        return $this->success([
            'deposit_id'   => $deposit->id,
            'invoice_no'   => $invoiceNo,
            'amount'       => $amount,
            'snap_token'   => $snap['snap_token'],
            'redirect_url' => $snap['redirect_url'],
            'client_key'   => config('services.midtrans.client_key'),
        ], 'Snap token berhasil dibuat', 201);
    }

    // ── Pay existing order ───────────────────────────────────────

    private function handleOrderPayment(User $user, array $v)
    {
        $order = Order::where('id', $v['order_id'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return $this->error('Order tidak ditemukan atau sudah dibayar.', 404);
        }

        $invoiceNo = 'MID-ORD-' . $order->id . '-' . time();

        $snap = $this->midtrans->createSnapToken([
            'order_id'         => $invoiceNo,
            'gross_amount'     => (int) $order->total_pay,
            'name'             => $order->product_name ?? ('Order #' . $order->id),
            'customer'         => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
            'enabled_payments' => $v['enabled_payments'] ?? null,
            'finish_url'       => config('app.url') . '/order/' . $order->id . '/finish',
        ]);

        if (!$snap) {
            return $this->error('Gagal membuat Snap token. Coba lagi nanti.', 502);
        }

        $deposit = Deposit::create([
            'user_id'               => $user->id,
            'order_id'              => $order->id,
            'invoice_number'        => $invoiceNo,
            'amount'                => $order->total_pay,
            'method'                => 'midtrans',
            'purpose'               => 'order_payment',
            'status'                => 'pending',
            'midtrans_snap_token'   => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
        ]);

        return $this->success([
            'order_id'     => $order->id,
            'deposit_id'   => $deposit->id,
            'invoice_no'   => $invoiceNo,
            'total_pay'    => $order->total_pay,
            'snap_token'   => $snap['snap_token'],
            'redirect_url' => $snap['redirect_url'],
            'client_key'   => config('services.midtrans.client_key'),
        ], 'Snap token berhasil dibuat', 201);
    }

    // ── Create order + pay ───────────────────────────────────────

    private function handleNewTransaction(User $user, array $v)
    {
        $basePrice    = (float) $v['base_price'];
        $markupAmount = 0;

        if (!$user->isExemptFromMarkup()) {
            $markup       = \App\Models\ProductMarkup::findMarkup('okeconnect', $v['product_code'], $v['category'] ?? null);
            $markupAmount = $markup ? $markup->calculateMarkup($basePrice) : 0;
        }

        $sellPrice = $basePrice + $markupAmount;
        $refId     = 'MID-TRX-' . time() . '-' . Str::random(6);
        $invoiceNo = 'MID-TRX-' . time();

        $order = Order::create([
            'user_id'        => $user->id,
            'provider'       => 'okeconnect',
            'order_ref'      => $refId,
            'product_code'   => $v['product_code'],
            'product_name'   => $v['product_name'] ?? null,
            'category'       => $v['category'] ?? null,
            'target'         => $v['destination'],
            'quantity'       => 1,
            'base_price'     => $basePrice,
            'markup'         => $markupAmount,
            'sell_price'     => $sellPrice,
            'profit'         => $markupAmount,
            'payment_method' => 'midtrans',
            'payment_fee'    => 0,
            'total_pay'      => $sellPrice,
            'payment_status' => 'belum',
            'status'         => 'pending',
        ]);

        $snap = $this->midtrans->createSnapToken([
            'order_id'         => $invoiceNo,
            'gross_amount'     => (int) $sellPrice,
            'name'             => $v['product_name'] ?? ('Top-up ' . $v['product_code']),
            'customer'         => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
            'enabled_payments' => $v['enabled_payments'] ?? null,
            'finish_url'       => config('app.url') . '/order/' . $order->id . '/finish',
        ]);

        if (!$snap) {
            $order->update(['status' => 'failed', 'notes' => 'Gagal generate Snap token']);
            return $this->error('Gagal membuat Snap token. Coba lagi nanti.', 502);
        }

        $deposit = Deposit::create([
            'user_id'               => $user->id,
            'order_id'              => $order->id,
            'invoice_number'        => $invoiceNo,
            'amount'                => $sellPrice,
            'method'                => 'midtrans',
            'purpose'               => 'order_payment',
            'status'                => 'pending',
            'midtrans_snap_token'   => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
        ]);

        return $this->success([
            'order_id'     => $order->id,
            'deposit_id'   => $deposit->id,
            'invoice_no'   => $invoiceNo,
            'sell_price'   => $sellPrice,
            'snap_token'   => $snap['snap_token'],
            'redirect_url' => $snap['redirect_url'],
            'client_key'   => config('services.midtrans.client_key'),
        ], 'Snap token berhasil dibuat', 201);
    }

    // ─────────────────────────────────────────────────────────────

    /**
     * POST /api/midtrans/webhook
     *
     * Midtrans payment notification handler.
     * This endpoint is called by Midtrans server — NO auth middleware.
     */
    public function webhook(Request $request)
    {
        $notif = $this->midtrans->parseWebhook();

        if (!$notif) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $midtransOrderId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? '';
        $transactionId = $notif->transaction_id ?? null;
        $paymentType = $notif->payment_type ?? null;
        $vaNumber = $notif->va_numbers[0]->va_number ?? ($notif->bill_key ?? null);

        Log::info('Midtrans webhook received', [
            'order_id'    => $midtransOrderId,
            'status'      => $transactionStatus,
            'fraud'       => $fraudStatus,
            'payment'     => $paymentType,
        ]);

        $deposit = Deposit::where('invoice_number', $midtransOrderId)->first();

        if (!$deposit) {
            Log::warning('Midtrans webhook: deposit not found', ['invoice' => $midtransOrderId]);
            return response()->json(['message' => 'Deposit not found'], 404);
        }

        // Skip if already processed
        if (in_array($deposit->status, ['paid', 'failed'])) {
            return response()->json(['message' => 'Already processed']);
        }

        $newStatus = $this->midtrans->mapStatus($transactionStatus, $fraudStatus);

        DB::transaction(function () use ($deposit, $newStatus, $transactionId, $paymentType, $vaNumber, $notif, $transactionStatus, $fraudStatus) {
            $deposit->update([
                'status'                   => $newStatus,
                'midtrans_transaction_id'  => $transactionId,
                'midtrans_payment_type'    => $paymentType,
                'midtrans_va_number'       => $vaNumber,
                'midtrans_response'        => (array) $notif,
                'paid_at'                  => $newStatus === 'paid' ? now() : null,
                'payment_method_by'        => $paymentType,
            ]);

            if ($newStatus === 'paid') {
                $this->processPaidDeposit($deposit);
            }
        });

        return response()->json(['message' => 'OK']);
    }

    private function processPaidDeposit(Deposit $deposit): void
    {
        $deposit->refresh();

        if ($deposit->purpose === 'deposit') {
            User::lockForUpdate()->find($deposit->user_id)?->increment('balance', (float) $deposit->amount);
            Log::info('Midtrans: balance credited', ['user_id' => $deposit->user_id, 'amount' => $deposit->amount]);

        } elseif ($deposit->purpose === 'order_payment' && $deposit->order_id) {
            $order = Order::find($deposit->order_id);
            if (!$order || $order->status !== 'pending') return;

            $order->update(['payment_status' => 'lunas']);
            $this->payment->fulfillOrder($order);
        }
    }

    // ─────────────────────────────────────────────────────────────

    /**
     * GET /api/midtrans/status/{invoiceNo}
     *
     * Check Midtrans transaction status by our invoice number.
     */
    public function status(Request $request, string $invoiceNo)
    {
        $deposit = Deposit::where('invoice_number', $invoiceNo)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$deposit) {
            return $this->error('Deposit tidak ditemukan', 404);
        }

        // Also pull live status from Midtrans
        $live = $this->midtrans->checkStatus($invoiceNo);

        if ($live) {
            $newStatus = $this->midtrans->mapStatus(
                $live['transaction_status'] ?? 'pending',
                $live['fraud_status'] ?? ''
            );

            if ($newStatus !== $deposit->status && !in_array($deposit->status, ['paid', 'failed'])) {
                $deposit->update([
                    'status'                  => $newStatus,
                    'midtrans_transaction_id' => $live['transaction_id'] ?? $deposit->midtrans_transaction_id,
                    'midtrans_payment_type'   => $live['payment_type'] ?? $deposit->midtrans_payment_type,
                    'paid_at'                 => $newStatus === 'paid' ? now() : $deposit->paid_at,
                ]);

                if ($newStatus === 'paid') {
                    $this->processPaidDeposit($deposit->fresh());
                }
            }
        }

        return $this->success([
            'deposit_id'     => $deposit->id,
            'invoice_no'     => $invoiceNo,
            'status'         => $deposit->fresh()->status,
            'amount'         => $deposit->amount,
            'payment_type'   => $deposit->midtrans_payment_type,
            'paid_at'        => $deposit->paid_at?->toIso8601String(),
            'order_id'       => $deposit->order_id,
        ]);
    }

    /**
     * POST /api/midtrans/cancel/{invoiceNo}
     *
     * Cancel a pending Midtrans transaction.
     */
    public function cancel(Request $request, string $invoiceNo)
    {
        $deposit = Deposit::where('invoice_number', $invoiceNo)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if (!$deposit) {
            return $this->error('Deposit tidak ditemukan atau sudah diproses', 404);
        }

        $result = $this->midtrans->cancel($invoiceNo);

        if ($result === null) {
            return $this->error('Gagal membatalkan transaksi', 502);
        }

        $deposit->update(['status' => 'failed', 'notes' => 'Dibatalkan oleh user']);

        return $this->success(['invoice_no' => $invoiceNo, 'status' => 'cancelled'], 'Transaksi dibatalkan');
    }
}
