<?php

namespace App\Http\Controllers\Api;

use App\Models\Deposit;
use App\Models\DigitalProduct;
use App\Models\Order;
use App\Models\User;
use App\Services\DigitalOrderService;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DigitalOrderController extends BaseApiController
{
    public function __construct(
        protected DigitalOrderService $digitalOrder,
        protected MidtransService $midtrans
    ) {}

    /**
     * POST /api/digital/order
     *
     * Beli produk digital (akun, voucher, dll).
     * payment_method: "balance" → langsung potong saldo, delivery di response.
     * payment_method: "qris"   → buat Midtrans Snap (QRIS), delivery setelah bayar.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_produk'    => 'required|string|max:50',
            'quantity'       => 'nullable|integer|min:1|max:10',
            'payment_method' => 'nullable|in:balance,qris',
        ]);

        $user          = $request->user();
        $kodeProduk    = $validated['kode_produk'];
        $quantity      = $validated['quantity'] ?? 1;
        $paymentMethod = $validated['payment_method'] ?? 'balance';
        $userType      = ($user->isAdmin() || $user->isReseller()) ? 'reseller' : 'user';
        $orderRef      = 'DIG-' . time() . '-' . Str::random(6);

        // Check product & price
        $product = DigitalProduct::where('kode_produk', $kodeProduk)->first();

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        if (!$product->is_active) {
            return $this->error('Produk sedang tidak aktif.', 422);
        }

        if ($product->stok < $quantity) {
            return $this->error("Stok tidak mencukupi. Tersedia: {$product->stok}, diminta: {$quantity}.", 422);
        }

        $price     = $product->getPriceFor($userType);
        $totalCost = $price * $quantity;

        if ($paymentMethod === 'qris') {
            return $this->processWithQris($user, $product, $kodeProduk, $quantity, $userType, $orderRef, $price, $totalCost);
        }

        return $this->processWithBalance($user, $product, $kodeProduk, $quantity, $userType, $orderRef, $price, $totalCost);
    }

    /**
     * Pay with user balance — deduct immediately, grab stock, return delivery.
     */
    private function processWithBalance(User $user, DigitalProduct $product, string $kodeProduk, int $quantity, string $userType, string $orderRef, int $price, int $totalCost): JsonResponse
    {
        if ($user->balance < $totalCost) {
            return $this->error(
                'Saldo tidak cukup. Dibutuhkan Rp' . number_format($totalCost, 0, ',', '.') .
                ', saldo Anda Rp' . number_format($user->balance, 0, ',', '.'),
                422
            );
        }

        try {
            $result = DB::transaction(function () use ($user, $product, $kodeProduk, $quantity, $userType, $orderRef, $totalCost, $price) {
                $lockedUser = User::lockForUpdate()->find($user->id);
                if ($lockedUser->balance < $totalCost) {
                    throw new \Exception('INSUFFICIENT_BALANCE');
                }

                $lockedUser->decrement('balance', $totalCost);

                $orderResult = $this->digitalOrder->placeOrder(
                    $kodeProduk, $quantity, $userType, $user->id, $orderRef
                );

                if (!$orderResult['success']) {
                    throw new \Exception($orderResult['message']);
                }

                $order = Order::create([
                    'user_id'        => $user->id,
                    'provider'       => 'digital',
                    'order_ref'      => $orderRef,
                    'product_code'   => $kodeProduk,
                    'product_name'   => $product->nama_produk,
                    'category'       => $product->category?->nama_kategori,
                    'target'         => '-',
                    'quantity'       => $quantity,
                    'base_price'     => $price,
                    'markup'         => 0,
                    'sell_price'     => $price,
                    'profit'         => 0,
                    'payment_method' => 'balance',
                    'payment_fee'    => 0,
                    'total_pay'      => $totalCost,
                    'payment_status' => 'lunas',
                    'status'         => 'completed',
                    'sn'             => is_array($orderResult['data']['delivery'])
                        ? implode("\n", $orderResult['data']['delivery'])
                        : $orderResult['data']['delivery'],
                ]);

                return [
                    'order'  => $order,
                    'result' => $orderResult,
                    'balance_remaining' => $lockedUser->fresh()->balance,
                ];
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                return $this->error('Saldo tidak cukup saat diproses.', 422);
            }
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'order_id'          => $result['order']->id,
            'order_ref'         => $orderRef,
            'produk'            => $product->nama_produk,
            'kode_produk'       => $kodeProduk,
            'quantity'          => $quantity,
            'harga_satuan'      => $price,
            'total_bayar'       => $totalCost,
            'payment_method'    => 'balance',
            'delivery'          => $result['result']['data']['delivery'],
            'sisa_stok'         => $result['result']['data']['sisa_stok'],
            'balance_remaining' => $result['balance_remaining'],
            'status'            => 'completed',
        ], 'Pembelian berhasil! Cek delivery untuk detail akun/voucher.');
    }

    /**
     * Pay with QRIS via Midtrans Snap — create pending order, return snap_token.
     * Delivery akan dikirim setelah pembayaran dikonfirmasi via webhook.
     */
    private function processWithQris(User $user, DigitalProduct $product, string $kodeProduk, int $quantity, string $userType, string $orderRef, int $price, int $totalCost): JsonResponse
    {
        if (!$this->midtrans->isEnabled()) {
            return $this->error('Pembayaran QRIS belum dikonfigurasi.', 503);
        }

        $invoiceNo = 'DIG-QRIS-' . time() . '-' . Str::random(4);

        $order = Order::create([
            'user_id'        => $user->id,
            'provider'       => 'digital',
            'order_ref'      => $orderRef,
            'product_code'   => $kodeProduk,
            'product_name'   => $product->nama_produk,
            'category'       => $product->category?->nama_kategori,
            'target'         => '-',
            'quantity'       => $quantity,
            'base_price'     => $price,
            'markup'         => 0,
            'sell_price'     => $price,
            'profit'         => 0,
            'payment_method' => 'qris',
            'payment_fee'    => 0,
            'total_pay'      => $totalCost,
            'payment_status' => 'belum',
            'status'         => 'pending',
        ]);

        $snap = $this->midtrans->createSnapToken([
            'order_id'         => $invoiceNo,
            'gross_amount'     => $totalCost,
            'name'             => substr('Digital: ' . $product->nama_produk, 0, 50),
            'customer'         => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
            'enabled_payments' => ['qris', 'gopay'],
            'finish_url'       => config('app.url') . '/order/' . $order->id . '/finish',
        ]);

        if (!$snap) {
            $order->update(['status' => 'failed', 'notes' => 'Gagal generate Snap token']);
            return $this->error('Gagal membuat pembayaran QRIS. Coba lagi nanti.', 502);
        }

        Deposit::create([
            'user_id'               => $user->id,
            'order_id'              => $order->id,
            'invoice_number'        => $invoiceNo,
            'amount'                => $totalCost,
            'method'                => 'midtrans',
            'purpose'               => 'order_payment',
            'status'                => 'pending',
            'midtrans_snap_token'   => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
        ]);

        return $this->success([
            'order_id'       => $order->id,
            'order_ref'      => $orderRef,
            'produk'         => $product->nama_produk,
            'kode_produk'    => $kodeProduk,
            'quantity'       => $quantity,
            'harga_satuan'   => $price,
            'total_bayar'    => $totalCost,
            'payment_method' => 'qris',
            'snap_token'     => $snap['snap_token'],
            'redirect_url'   => $snap['redirect_url'],
            'client_key'     => config('services.midtrans.client_key'),
            'status'         => 'pending',
            'message_info'   => 'Scan QRIS untuk bayar. Setelah pembayaran dikonfirmasi, akun/voucher akan tersedia di detail order.',
        ], 'Silakan selesaikan pembayaran via QRIS.', 201);
    }
}
