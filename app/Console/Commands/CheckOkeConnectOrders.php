<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OkeConnectService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOkeConnectOrders extends Command
{
    protected $signature   = 'okeconnect:check-orders';
    protected $description = 'Check status of pending OkeConnect orders and update them';

    public function handle(OkeConnectService $okeConnect): void
    {
        // Only check orders that have been processing for >2 min and <24 hours
        $orders = Order::where('provider', 'okeconnect')
            ->where('status', 'processing')
            ->where('created_at', '>=', now()->subHours(24))
            ->where('created_at', '<=', now()->subMinutes(2))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No processing OkeConnect orders to check.');
            return;
        }

        $this->info("Checking {$orders->count()} order(s)...");

        foreach ($orders as $order) {
            try {
                $qty = $order->category === 'ewallet' ? (int) $order->base_price : null;

                $result = $okeConnect->checkTransactionStatus(
                    $order->product_code,
                    $order->target,
                    $order->order_ref,
                    $qty
                );

                if ($result === null) {
                    $this->warn("Order #{$order->id}: provider unreachable, skipped.");
                    continue;
                }

                $newStatus = match ($result['status']) {
                    'success'              => 'completed',
                    'failed', 'error_ip'  => 'failed',
                    'not_found'           => 'failed',
                    default               => null, // still processing, no change
                };

                if ($newStatus === null) {
                    $this->line("Order #{$order->id}: still {$result['status']}.");
                    continue;
                }

                $order->update([
                    'status'            => $newStatus,
                    'notes'             => $result['raw'] ?? $order->notes,
                    'provider_response' => $result,
                ]);

                // Refund on failure if paid via balance
                if ($newStatus === 'failed' && $order->payment_method === 'balance' && $order->payment_status === 'lunas') {
                    $order->user()->increment('balance', $order->sell_price);
                    $order->update(['notes' => 'Auto-refund via scheduler: ' . ($result['raw'] ?? '')]);
                    Log::info("CheckOkeConnect: refunded order #{$order->id}, amount {$order->sell_price}");
                }

                $this->info("Order #{$order->id}: updated to {$newStatus}.");
                Log::info("CheckOkeConnect: order #{$order->id} → {$newStatus}", ['raw' => $result['raw'] ?? '']);

            } catch (\Exception $e) {
                $this->error("Order #{$order->id}: exception — {$e->getMessage()}");
                Log::error("CheckOkeConnect exception for order #{$order->id}", ['error' => $e->getMessage()]);
            }
        }

        $this->info('Done.');
    }
}
