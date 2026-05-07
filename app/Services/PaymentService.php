<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\QrisFee;
use App\Models\QrisMarkupSetting;
use App\Models\User;
use App\Services\OkeConnectService;
use App\Services\SmmPanelService;
use Illuminate\Support\Str;

class PaymentService
{
    protected QrisService $qris;

    public function __construct(QrisService $qris)
    {
        $this->qris = $qris;
    }

    /**
     * Calculate QRIS markup for a given amount and purpose.
     * Admin and Reseller are exempt from QRIS markup.
     *
     * @param float  $amount  Base amount
     * @param string $purpose 'deposit' or 'transaction'
     * @param User|null $user The user making the payment
     */
    public function calculateQrisFee(float $amount, string $purpose = 'transaction', ?User $user = null): float
    {
        // Admin and Reseller are exempt from markup
        if ($user && $user->isExemptFromQrisFee()) {
            return 0;
        }

        $setting = QrisMarkupSetting::current();

        return $setting->calculateMarkup($amount, $purpose);
    }

    /**
     * Check if QRIS payment is enabled (PayinAja API key configured).
     */
    public function isQrisEnabled(): bool
    {
        return !empty(config('services.payinaja.api_key'));
    }

    /**
     * Create a QRIS payment for an order via PayinAja.
     *
     * @return Deposit|null Returns the deposit record or null on failure.
     */
    public function createQrisForOrder(Order $order, float $totalPay): ?Deposit
    {
        $invoiceNumber = 'QR-' . $order->id . '-' . time() . '-' . Str::random(4);

        $qrisData = $this->qris->createQris(
            $invoiceNumber,
            (int) ceil($totalPay),
            $order->user?->name
        );

        if ($qrisData === null) {
            return null;
        }

        // PayinAja QRIS expires in 15 minutes
        $expiredAt = now()->addMinutes(15);

        return Deposit::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $totalPay,
            'method' => 'qris',
            'purpose' => 'order_payment',
            'status' => 'pending',
            'qris_content' => $qrisData['qris_string'] ?? null,
            'qris_image_url' => $qrisData['qris_image_url'] ?? null,
            'payinaja_trx_id' => $qrisData['payinaja_trx_id'] ?? null,
            'payinaja_fee' => $qrisData['fee'] ?? null,
            'payinaja_total' => $qrisData['total_amount'] ?? null,
            'qris_request_date' => now(),
            'qris_expired_at' => $expiredAt,
        ]);
    }

    /**
     * Create a QRIS payment for a deposit (top up saldo) via PayinAja.
     */
    public function createQrisForDeposit(int $userId, int $amount, ?string $customerName = null): ?Deposit
    {
        $invoiceNumber = 'DEP-' . time() . '-' . Str::random(6);

        $qrisData = $this->qris->createQris($invoiceNumber, $amount, $customerName);

        if ($qrisData === null) {
            return null;
        }

        // PayinAja QRIS expires in 15 minutes
        $expiredAt = now()->addMinutes(15);

        return Deposit::create([
            'user_id' => $userId,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'method' => 'qris',
            'purpose' => 'deposit',
            'status' => 'pending',
            'qris_content' => $qrisData['qris_string'] ?? null,
            'qris_image_url' => $qrisData['qris_image_url'] ?? null,
            'payinaja_trx_id' => $qrisData['payinaja_trx_id'] ?? null,
            'payinaja_fee' => $qrisData['fee'] ?? null,
            'payinaja_total' => $qrisData['total_amount'] ?? null,
            'qris_request_date' => now(),
            'qris_expired_at' => $expiredAt,
        ]);
    }

    /**
     * Check QRIS payment via PayinAja and process if paid.
     *
     * @return array ['status' => 'paid'|'unpaid'|'expired'|'error', ...]
     */
    public function checkAndProcessQris(Deposit $deposit): array
    {
        // Already paid
        if ($deposit->status === 'paid') {
            return [
                'status' => 'paid',
                'deposit' => $deposit,
            ];
        }

        // Expired (local check)
        if ($deposit->isExpired()) {
            $deposit->update(['status' => 'expired']);

            // If linked to an order, mark it failed
            if ($deposit->order_id) {
                $deposit->order?->update(['status' => 'failed', 'notes' => 'QRIS expired']);
            }

            return ['status' => 'expired'];
        }

        // No PayinAja trx ID means we can't check
        if (empty($deposit->payinaja_trx_id)) {
            return ['status' => 'error', 'message' => 'Tidak ada ID transaksi PayinAja'];
        }

        // Check with PayinAja API
        $result = $this->qris->checkStatus($deposit->payinaja_trx_id);

        if ($result === null) {
            return ['status' => 'error', 'message' => 'Gagal memeriksa status pembayaran'];
        }

        $payinajaStatus = $result['status'] ?? 'pending';

        if ($payinajaStatus === 'success') {
            $deposit->update([
                'status' => 'paid',
                'payment_customer_name' => $result['customer_name'] ?? null,
                'payment_method_by' => $result['payment_method'] ?? 'QRIS',
                'paid_at' => now(),
            ]);

            // Process based on purpose
            if ($deposit->purpose === 'deposit') {
                // Top up saldo
                $deposit->user->increment('balance', $deposit->amount);
            } elseif ($deposit->purpose === 'order_payment' && $deposit->order_id) {
                // Mark order as paid then fulfill
                $deposit->order?->update(['payment_status' => 'lunas']);
                $this->fulfillOrder($deposit->order);
            }

            return [
                'status' => 'paid',
                'deposit' => $deposit->fresh(),
                'payment_by' => $result['payment_method'] ?? 'QRIS',
                'customer_name' => $result['customer_name'] ?? null,
            ];
        }

        if ($payinajaStatus === 'failed') {
            $deposit->update(['status' => 'expired']);

            if ($deposit->order_id) {
                $deposit->order?->update(['status' => 'failed', 'notes' => 'Pembayaran QRIS gagal/expired']);
            }

            return ['status' => 'expired'];
        }

        // Still pending
        return ['status' => 'unpaid'];
    }

    /**
     * Fulfill an order after QRIS payment confirmed.
     * This sends the order to the actual provider (OkeConnect / SMM Panel).
     */
    protected function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'processing']);

        if ($order->provider === 'okeconnect') {
            $service = app(OkeConnectService::class);

            // Ewallet nominal bebas uses qty param (base_price = nominal amount)
            if ($order->category === 'ewallet') {
                $result = $service->createNominalBebasTransaction(
                    $order->product_code,
                    $order->target,
                    (int) $order->base_price,
                    $order->order_ref
                );
            } else {
                $result = $service->createTransaction(
                    $order->product_code,
                    $order->target,
                    $order->order_ref
                );
            }

            if ($result === null) {
                $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon setelah pembayaran QRIS']);
                return;
            }

            $providerStatus = $result['status'] ?? 'processing';
            $orderStatus = match ($providerStatus) {
                'success'             => 'completed',
                'failed', 'error_ip' => 'failed',
                default              => 'processing',
            };

            $order->update(array_filter([
                'provider_response' => $result,
                'notes'             => $result['raw'] ?? null,
                'status'            => $orderStatus,
                'sn'                => $result['sn'] ?? null,
            ], fn($v) => $v !== null));

        } elseif ($order->provider === 'smmpanel') {
            $service = app(SmmPanelService::class);
            $result = $service->order(
                (int) $order->product_code,
                $order->target,
                $order->quantity
            );

            if ($result === null) {
                $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon setelah pembayaran QRIS']);
                return;
            }

            $providerOrderId = $result['order'] ?? $result['data']['order'] ?? null;
            $order->update([
                'provider_response' => $result,
                'order_ref'         => $providerOrderId ? (string) $providerOrderId : $order->order_ref,
                'status'            => isset($result['error']) ? 'failed' : 'processing',
            ]);
        }
    }
}
