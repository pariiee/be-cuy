<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\ProductMarkup;
use App\Models\User;
use App\Services\OkeConnectService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends BaseApiController
{
    public function __construct(
        protected OkeConnectService $okeConnect,
        protected PaymentService $payment
    ) {}

    /**
     * POST /api/transactions
     *
     * Create a new top-up transaction (OkeConnect).
     *
     * Body: {
     *   "product_code": "S10",
     *   "destination": "08123456789",
     *   "product_name": "Telkomsel 10rb",
     *   "category": "pulsa",
     *   "base_price": 10000,
     *   "payment_method": "balance"  // "balance" or "qris"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code'   => 'required|string|max:20',
            'destination'    => ['required', 'string', 'max:50', 'regex:/^[0-9]{6,20}$/'],
            'product_name'   => 'nullable|string|max:255',
            'category'       => 'nullable|string|max:100',
            'base_price'     => 'required|numeric|min:0',
            'payment_method' => 'required|in:balance,qris',
        ], [
            'destination.regex' => 'Nomor tujuan harus berupa angka (6-20 digit).',
        ]);

        $user          = $request->user();
        $basePrice     = (float) $validated['base_price'];
        $paymentMethod = $validated['payment_method'];

        // Admin & Reseller bypass product markup
        $markupAmount = 0;
        if (!$user->isExemptFromMarkup()) {
            $markupModel  = ProductMarkup::findMarkup('okeconnect', $validated['product_code'], $validated['category'] ?? null);
            $markupAmount = $markupModel ? $markupModel->calculateMarkup($basePrice) : 0;
        }
        $sellPrice = $basePrice + $markupAmount;

        // Calculate QRIS fee if applicable
        $paymentFee = 0;
        if ($paymentMethod === 'qris') {
            if (!$this->payment->isQrisEnabled()) {
                return $this->error('Pembayaran QRIS sedang tidak tersedia', 422);
            }
            $paymentFee = $this->payment->calculateQrisFee($sellPrice, 'transaction', $user);
        }

        $totalPay = $sellPrice + $paymentFee;
        $refId = 'TRX-' . time() . '-' . Str::random(6);

        if ($paymentMethod === 'balance') {
            return $this->processWithBalance($user, $validated, $basePrice, $markupAmount, $sellPrice, $paymentFee, $totalPay, $refId);
        }

        return $this->processWithQris($user, $validated, $basePrice, $markupAmount, $sellPrice, $paymentFee, $totalPay, $refId);
    }

    /**
     * Pay with user balance — deduct immediately and send to provider.
     */
    private function processWithBalance($user, array $validated, float $basePrice, float $markupAmount, float $sellPrice, float $paymentFee, float $totalPay, string $refId)
    {
        // Quick pre-check (not final, real check inside DB lock below)
        if ($user->balance < $sellPrice) {
            return $this->error('Saldo tidak cukup. Dibutuhkan Rp' . number_format($sellPrice, 0, ',', '.') . ', saldo Anda Rp' . number_format($user->balance, 0, ',', '.'), 422);
        }

        try {
        $order = DB::transaction(function () use ($user, $validated, $basePrice, $markupAmount, $sellPrice, $refId) {
            // Lock the row to prevent concurrent double-spend
            $lockedUser = User::lockForUpdate()->find($user->id);
            if ($lockedUser->balance < $sellPrice) {
                throw new \Exception('INSUFFICIENT_BALANCE');
            }
            $lockedUser->decrement('balance', $sellPrice);

            return Order::create([
                'user_id' => $user->id,
                'provider' => 'okeconnect',
                'order_ref' => $refId,
                'product_code' => $validated['product_code'],
                'product_name' => $validated['product_name'] ?? null,
                'category' => $validated['category'] ?? null,
                'target' => $validated['destination'],
                'quantity' => 1,
                'base_price' => $basePrice,
                'markup' => $markupAmount,
                'sell_price' => $sellPrice,
                'profit' => $markupAmount,
                'payment_method' => 'balance',
                'payment_fee' => 0,
                'total_pay' => $sellPrice,
                'payment_status' => 'lunas',
                'status' => 'processing',
            ]);
        });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                return $this->error('Saldo tidak cukup. Dibutuhkan Rp' . number_format($sellPrice, 0, ',', '.'), 422);
            }
            throw $e;
        }

        // Send to provider
        $result = $this->okeConnect->createTransaction(
            $validated['product_code'],
            $validated['destination'],
            $refId
        );

        if ($result === null) {
            $user->increment('balance', $sellPrice);
            $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon']);
            return $this->error('Gagal memproses transaksi, saldo dikembalikan', 502);
        }

        $providerStatus = $result['status'] ?? 'processing';
        $orderStatus    = match ($providerStatus) {
            'success'              => 'completed',
            'failed', 'error_ip'  => 'failed',
            default               => 'processing',
        };

        $order->update(array_filter([
            'provider_response' => $result,
            'notes'             => $result['raw'] ?? null,
            'status'            => $orderStatus,
            'sn'                => $result['sn'] ?? null,
        ], fn($v) => $v !== null));

        if ($orderStatus === 'failed') {
            $user->increment('balance', $sellPrice);
        }

        $fresh = $order->fresh();
        return $this->success([
            'order_id'          => $fresh->id,
            'ref_id'            => $refId,
            'payment_method'    => 'balance',
            'sell_price'        => $sellPrice,
            'status'            => $fresh->status,
            'sn'                => $fresh->sn,
            'balance_remaining' => $user->fresh()->balance,
        ], 'Transaksi diproses');
    }

    /**
     * Pay with QRIS — create order as pending, generate QR, wait for payment.
     */
    private function processWithQris($user, array $validated, float $basePrice, float $markupAmount, float $sellPrice, float $paymentFee, float $totalPay, string $refId)
    {
        // Create order as pending (waiting for QRIS payment)
        $order = Order::create([
            'user_id' => $user->id,
            'provider' => 'okeconnect',
            'order_ref' => $refId,
            'product_code' => $validated['product_code'],
            'product_name' => $validated['product_name'] ?? null,
            'category' => $validated['category'] ?? null,
            'target' => $validated['destination'],
            'quantity' => 1,
            'base_price' => $basePrice,
            'markup' => $markupAmount,
            'sell_price' => $sellPrice,
            'profit' => $markupAmount,
            'payment_method' => 'qris',
            'payment_fee' => $paymentFee,
            'total_pay' => $totalPay,
            'payment_status' => 'belum',
            'status' => 'pending',
        ]);

        // Generate QRIS
        $deposit = $this->payment->createQrisForOrder($order, $totalPay);

        if ($deposit === null) {
            $order->update(['status' => 'failed', 'notes' => 'Gagal generate QRIS']);
            return $this->error('Gagal membuat QRIS. Coba lagi nanti.', 502);
        }

        return $this->success([
            'order_id' => $order->id,
            'ref_id' => $refId,
            'payment_method' => 'qris',
            'sell_price' => $sellPrice,
            'payment_fee' => $paymentFee,
            'total_pay' => $totalPay,
            'qris_content' => $deposit->qris_content,
            'qris_image_url' => $deposit->qris_image_url,
            'payinaja_trx_id' => $deposit->payinaja_trx_id,
            'qris_expired_at' => $deposit->qris_expired_at->toIso8601String(),
            'deposit_id' => $deposit->id,
            'status' => 'pending',
        ], 'Silakan scan QRIS untuk pembayaran', 201);
    }
}
