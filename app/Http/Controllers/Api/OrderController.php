<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    /**
     * GET /api/orders
     *
     * Get user's order history with optional filters.
     * Query: ?status=completed&provider=smmpanel&page=1
     */
    public function index(Request $request)
    {
        $query = $request->user()->orders()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->query('provider'));
        }

        $orders = $query->paginate(20);

        return $this->success($orders);
    }

    /**
     * GET /api/orders/{order}
     *
     * Get single order detail.
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order tidak ditemukan', 404);
        }

        return $this->success($order);
    }

    /**
     * GET /api/orders/{order}/status
     *
     * Lightweight status polling — frontend polls this after submitting a transaction.
     */
    public function status(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order tidak ditemukan', 404);
        }

        return $this->success([
            'order_id'       => $order->id,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'sn'             => $order->sn,
            'notes'          => $order->notes,
            'updated_at'     => $order->updated_at->toIso8601String(),
        ]);
    }

    /**
     * GET /api/orders/{order}/invoice
     *
     * Get structured invoice data for an order.
     */
    public function invoice(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order tidak ditemukan', 404);
        }

        $user = $request->user();

        $statusLabel = match ($order->status) {
            'completed'  => 'Selesai',
            'processing' => 'Diproses',
            'pending'    => 'Menunggu Pembayaran',
            'failed'     => 'Gagal',
            'refunded'   => 'Dikembalikan',
            default      => ucfirst($order->status),
        };

        $paymentMethodLabel = match ($order->payment_method) {
            'balance'  => 'Saldo',
            'midtrans' => 'Midtrans',
            default    => $order->payment_method,
        };

        $paymentStatusLabel = match ($order->payment_status) {
            'lunas' => 'Lunas',
            'belum' => 'Belum Lunas',
            default => '-',
        };

        return $this->success([
            'invoice_number' => 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'order_id'       => $order->id,
            'ref_id'         => $order->order_ref,
            'date'           => $order->created_at->toIso8601String(),
            'customer'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'item'           => [
                'provider'     => $order->provider,
                'product_code' => $order->product_code,
                'product_name' => $order->product_name,
                'category'     => $order->category,
                'target'       => $order->target,
                'quantity'     => $order->quantity,
            ],
            'pricing'        => [
                'base_price'   => (float) $order->base_price,
                'markup'       => (float) $order->markup,
                'sell_price'   => (float) $order->sell_price,
                'payment_fee'  => (float) $order->payment_fee,
                'total_pay'    => (float) $order->total_pay,
            ],
            'payment'        => [
                'method'        => $order->payment_method,
                'method_label'  => $paymentMethodLabel,
                'status'        => $order->payment_status,
                'status_label'  => $paymentStatusLabel,
            ],
            'status'         => $order->status,
            'status_label'   => $statusLabel,
            'notes'          => $order->notes,
        ]);
    }
}
