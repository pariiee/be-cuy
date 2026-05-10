<?php

namespace App\Services;

use App\Models\DigitalProduct;
use App\Models\Order;
use App\Services\OkeConnectService;
use App\Services\SmmPanelService;

class PaymentService
{
    /**
     * Fulfill an order after payment is confirmed.
     * Sends the order to the actual provider (OkeConnect / SMM Panel).
     */
    public function fulfillOrder(Order $order): void
    {
        $order->update(['status' => 'processing']);

        if ($order->provider === 'okeconnect') {
            $service = app(OkeConnectService::class);

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
                $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon setelah pembayaran']);
                return;
            }

            $orderStatus = match ($result['status'] ?? 'processing') {
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
            $result  = $service->order(
                (int) $order->product_code,
                $order->target,
                $order->quantity
            );

            if ($result === null) {
                $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon setelah pembayaran']);
                return;
            }

            $providerOrderId = $result['order'] ?? $result['data']['order'] ?? null;
            $order->update([
                'provider_response' => $result,
                'order_ref'         => $providerOrderId ? (string) $providerOrderId : $order->order_ref,
                'status'            => isset($result['error']) ? 'failed' : 'processing',
            ]);

        } elseif ($order->provider === 'digital') {
            $digitalService = app(DigitalOrderService::class);
            $userType = $order->user?->isReseller() ? 'reseller' : 'user';

            $result = $digitalService->placeOrder(
                $order->product_code,
                $order->quantity,
                $userType,
                $order->user_id,
                $order->order_ref
            );

            if (!$result['success']) {
                $order->update(['status' => 'failed', 'notes' => $result['message']]);
                return;
            }

            $delivery = $result['data']['delivery'];
            $order->update([
                'status' => 'completed',
                'sn'     => is_array($delivery) ? implode("\n", $delivery) : $delivery,
            ]);
        }
    }
}
