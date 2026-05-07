<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OkeConnectCallbackController extends BaseApiController
{
    public function __construct(protected OkeConnectService $okeConnect) {}

    /**
     * GET /api/okeconnect/callback
     *
     * OkeConnect sends: ?refid=114&message=T#...SUKSES...SN:...Saldo...
     * We parse the message, find the matching order, and update its status.
     */
    public function handle(Request $request)
    {
        $refid   = $request->query('refid');
        $message = $request->query('message');

        Log::info('OkeConnect callback received', [
            'refid'   => $refid,
            'message' => $message,
            'ip'      => $request->ip(),
        ]);

        if (!$refid || !$message) {
            return response('missing params', 400);
        }

        $parsed = $this->okeConnect->parseResponse($message);

        // Find order by order_ref — refID we sent is the order_ref
        $order = Order::where('order_ref', 'like', '%-' . $refid)
            ->orWhere('order_ref', $refid)
            ->first();

        if (!$order) {
            Log::warning('OkeConnect callback: order not found', ['refid' => $refid]);
            return response('ok', 200);
        }

        // Only update if order is still in a non-final state
        if (in_array($order->status, ['completed', 'failed'])) {
            return response('ok', 200);
        }

        $orderStatus = match ($parsed['status']) {
            'success'              => 'completed',
            'failed', 'error_ip'  => 'failed',
            default               => 'processing',
        };

        $order->update(array_filter([
            'provider_response' => $parsed,
            'notes'             => $message,
            'status'            => $orderStatus,
            'sn'                => $parsed['sn'] ?? null,
        ], fn($v) => $v !== null));

        // If failed and was paid via balance, refund saldo
        if ($orderStatus === 'failed' && $order->payment_method === 'balance' && $order->payment_status === 'lunas') {
            $order->user()->increment('balance', $order->sell_price);
            $order->update(['notes' => 'Refunded via callback: ' . $message]);
            Log::info('OkeConnect callback: balance refunded', ['order_id' => $order->id, 'amount' => $order->sell_price]);
        }

        Log::info('OkeConnect callback: order updated', [
            'order_id' => $order->id,
            'status'   => $orderStatus,
            'sn'       => $parsed['sn'] ?? null,
        ]);

        return response('ok', 200);
    }
}
