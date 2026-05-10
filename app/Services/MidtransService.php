<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$clientKey    = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = config('services.midtrans.is_sanitized', true);
        Config::$is3ds        = config('services.midtrans.is_3ds', true);
    }

    /**
     * Create a Snap payment token.
     *
     * @param array $params {
     *   order_id:     string  — unique order ID sent to Midtrans (e.g. "DEP-123-1234567890")
     *   gross_amount: int     — total amount in IDR
     *   name:         string  — item / description name
     *   customer:     array   — { first_name, email, phone }
     *   enabled_payments: array|null — e.g. ['gopay','qris','bank_transfer']
     * }
     * @return array|null { snap_token, redirect_url } or null on failure
     */
    public function createSnapToken(array $params): ?array
    {
        try {
            $payload = [
                'transaction_details' => [
                    'order_id'     => $params['order_id'],
                    'gross_amount' => (int) $params['gross_amount'],
                ],
                'item_details' => [[
                    'id'       => $params['order_id'],
                    'price'    => (int) $params['gross_amount'],
                    'quantity' => 1,
                    'name'     => substr($params['name'] ?? 'Pembayaran', 0, 50),
                ]],
                'customer_details' => [
                    'first_name' => $params['customer']['first_name'] ?? 'User',
                    'email'      => $params['customer']['email'] ?? '',
                    'phone'      => $params['customer']['phone'] ?? '',
                ],
                'callbacks' => [
                    'finish' => $params['finish_url'] ?? config('app.url'),
                ],
            ];

            // Optionally restrict payment methods
            if (!empty($params['enabled_payments'])) {
                $payload['enabled_payments'] = $params['enabled_payments'];
            }

            $snapResponse = Snap::createTransaction($payload);

            return [
                'snap_token'   => $snapResponse->token,
                'redirect_url' => $snapResponse->redirect_url,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans createSnapToken error', [
                'order_id' => $params['order_id'] ?? null,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check transaction status by Midtrans order_id.
     *
     * @return array|null Midtrans status response or null on failure
     */
    public function checkStatus(string $orderId): ?array
    {
        try {
            $status = Transaction::status($orderId);
            return (array) $status;
        } catch (\Exception $e) {
            Log::error('Midtrans checkStatus error', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cancel a pending Midtrans transaction.
     */
    public function cancel(string $orderId): ?array
    {
        try {
            $result = Transaction::cancel($orderId);
            return (array) $result;
        } catch (\Exception $e) {
            Log::error('Midtrans cancel error', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Refund a transaction (full or partial).
     *
     * @param string $orderId    Midtrans order_id
     * @param int|null $amount   Partial refund amount. null = full refund.
     * @param string $reason
     */
    public function refund(string $orderId, ?int $amount = null, string $reason = 'Refund'): ?array
    {
        try {
            $params = ['reason' => $reason];
            if ($amount !== null) {
                $params['amount'] = $amount;
            }
            $result = Transaction::refund($orderId, $params);
            return (array) $result;
        } catch (\Exception $e) {
            Log::error('Midtrans refund error', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Parse and verify a Midtrans webhook notification.
     * Returns the notification object or null if signature is invalid.
     */
    public function parseWebhook(): ?object
    {
        try {
            $notif = new Notification();

            // Verify signature key
            $signatureKey = hash('sha512',
                $notif->order_id .
                $notif->status_code .
                $notif->gross_amount .
                config('services.midtrans.server_key')
            );

            if ($signatureKey !== $notif->signature_key) {
                Log::warning('Midtrans webhook: invalid signature', ['order_id' => $notif->order_id]);
                return null;
            }

            return $notif;

        } catch (\Exception $e) {
            Log::error('Midtrans parseWebhook error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Map Midtrans transaction_status to our internal deposit status.
     */
    public function mapStatus(string $transactionStatus, string $fraudStatus = ''): string
    {
        return match ($transactionStatus) {
            'capture'   => ($fraudStatus === 'accept' || $fraudStatus === '') ? 'paid' : 'failed',
            'settlement' => 'paid',
            'pending'   => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default     => 'pending',
        };
    }

    /**
     * Check if Midtrans is configured and enabled.
     */
    public function isEnabled(): bool
    {
        return !empty(config('services.midtrans.server_key'));
    }
}
