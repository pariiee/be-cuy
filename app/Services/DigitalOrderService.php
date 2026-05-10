<?php

namespace App\Services;

use App\Models\DigitalProduct;
use Illuminate\Support\Facades\DB;

class DigitalOrderService
{
    /**
     * Validate and process a digital product order.
     *
     * Checks:
     * 1. Product exists and is active
     * 2. Product has sufficient stock (stok > 0)
     * 3. Decreases stock atomically
     *
     * @param string $kodeProduk  Kode produk yang dipesan
     * @param int    $quantity    Jumlah yang dipesan (default: 1)
     * @param string $userType    'user' atau 'reseller' untuk menentukan harga
     *
     * @return array{success: bool, data: array|null, message: string}
     */
    public function placeOrder(
        string $kodeProduk,
        int $quantity = 1,
        string $userType = 'user',
        ?int $userId = null,
        ?string $orderRef = null
    ): array {
        return DB::transaction(function () use ($kodeProduk, $quantity, $userType, $userId, $orderRef) {
            $product = DigitalProduct::where('kode_produk', $kodeProduk)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                return $this->fail('Produk tidak ditemukan.');
            }

            if (!$product->is_active) {
                return $this->fail('Produk sedang tidak aktif.');
            }

            if ($product->stok <= 0) {
                return $this->fail('Stok produk habis. Silakan hubungi admin untuk restock.');
            }

            if ($product->stok < $quantity) {
                return $this->fail("Stok tidak mencukupi. Tersedia: {$product->stok}, diminta: {$quantity}.");
            }

            // Grab stock items (one per quantity)
            $deliveryItems = [];
            for ($i = 0; $i < $quantity; $i++) {
                $item = $product->grabStockItem($userId, $orderRef);
                if (!$item) {
                    return $this->fail('Stok produk habis saat diproses.');
                }
                $deliveryItems[] = $item->content;
            }

            $price      = $product->getPriceFor($userType);
            $totalPrice = $price * $quantity;
            $fresh      = $product->fresh();

            return [
                'success' => true,
                'data'    => [
                    'product'    => $fresh->toApiArray($userType),
                    'quantity'   => $quantity,
                    'unit_price' => $price,
                    'total_price'=> $totalPrice,
                    'delivery'   => count($deliveryItems) === 1 ? $deliveryItems[0] : $deliveryItems,
                    'sisa_stok'  => $fresh->stok,
                ],
                'message' => 'Order berhasil diproses!',
            ];
        });
    }

    /**
     * Check product availability (without ordering).
     */
    public function checkAvailability(string $kodeProduk): array
    {
        $product = DigitalProduct::where('kode_produk', $kodeProduk)->first();

        if (!$product) {
            return $this->fail('Produk tidak ditemukan.');
        }

        return [
            'success' => true,
            'data'    => [
                'kode_produk' => $product->kode_produk,
                'nama_produk' => $product->nama_produk,
                'stok'        => $product->stok,
                'available'   => $product->hasStock() && $product->is_active,
                'is_active'   => $product->is_active,
            ],
            'message' => $product->hasStock()
                ? "Stok tersedia: {$product->stok}"
                : 'Stok habis.',
        ];
    }

    /**
     * Helper: Return fail response.
     */
    private function fail(string $message): array
    {
        return [
            'success' => false,
            'data'    => null,
            'message' => $message,
        ];
    }
}
