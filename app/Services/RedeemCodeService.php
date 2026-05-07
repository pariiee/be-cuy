<?php

namespace App\Services;

use App\Models\DigitalProduct;
use App\Models\RedeemCode;
use App\Models\RedeemCodeUsage;

class RedeemCodeService
{
    /**
     * Validate and apply a redeem code to a product.
     *
     * @param string      $code        Kode redeem yang dimasukkan user
     * @param string      $productCode Kode produk target
     * @param int         $userId      ID user yang menggunakan
     * @param int         $price       Harga asli produk
     * @return array      Result with status, data, and message
     */
    public function apply(string $code, string $productCode, int $userId, int $price = 0): array
    {
        // 1. Find the redeem code
        $redeemCode = RedeemCode::where('code', strtoupper($code))->first();

        if (!$redeemCode) {
            return $this->fail('Kode redeem tidak ditemukan.');
        }

        // 2. Validate code is still valid
        if (!$redeemCode->isValid()) {
            return $this->fail('Kode redeem sudah tidak aktif atau kuota habis.');
        }

        // 3. Check if code applies to this product
        if (!$redeemCode->isApplicableTo($productCode)) {
            return $this->fail('Kode redeem tidak berlaku untuk produk ini.');
        }

        // 4. Check if user already used this code (optional per-user limit)
        $alreadyUsed = RedeemCodeUsage::where('redeem_code_id', $redeemCode->id)
            ->where('user_id', $userId)
            ->where('product_code', $productCode)
            ->exists();

        if ($alreadyUsed) {
            return $this->fail('Anda sudah menggunakan kode ini untuk produk ini.');
        }

        // 5. Apply the code
        $output = $redeemCode->getOutput($price);

        // 6. Record usage
        RedeemCodeUsage::create([
            'redeem_code_id'  => $redeemCode->id,
            'user_id'         => $userId,
            'product_code'    => $productCode,
            'discount_applied' => $output['discount_value'] ?? 0,
            'output_message'  => $output['message'],
        ]);

        // 7. Increment usage counter
        $redeemCode->incrementUsage();

        return [
            'success' => true,
            'data'    => $output,
            'message' => $output['message'],
        ];
    }

    /**
     * Validate a redeem code without applying it.
     * Useful for previewing discount before checkout.
     */
    public function validate(string $code, ?string $productCode = null): array
    {
        $redeemCode = RedeemCode::where('code', strtoupper($code))->first();

        if (!$redeemCode) {
            return $this->fail('Kode redeem tidak ditemukan.');
        }

        if (!$redeemCode->isValid()) {
            return $this->fail('Kode redeem sudah tidak aktif atau kuota habis.');
        }

        if ($productCode && !$redeemCode->isApplicableTo($productCode)) {
            return $this->fail('Kode redeem tidak berlaku untuk produk ini.');
        }

        return [
            'success' => true,
            'data'    => [
                'code'               => $redeemCode->code,
                'type'               => $redeemCode->type,
                'discount_value'     => $redeemCode->discount_value,
                'applicable_products' => $redeemCode->applicable_products,
                'remaining_usage'    => $redeemCode->max_usage > 0
                    ? $redeemCode->max_usage - $redeemCode->used_count
                    : 'unlimited',
            ],
            'message' => 'Kode redeem valid!',
        ];
    }

    /**
     * Admin: Create a new redeem code.
     */
    public function create(array $data): array
    {
        // Validate max applicable products
        if (!empty($data['applicable_products']) && count($data['applicable_products']) > RedeemCode::MAX_APPLICABLE_PRODUCTS) {
            return $this->fail('Maksimal ' . RedeemCode::MAX_APPLICABLE_PRODUCTS . ' produk per kode redeem.');
        }

        // Validate applicable product codes exist
        if (!empty($data['applicable_products'])) {
            $existingCodes = DigitalProduct::whereIn('kode_produk', $data['applicable_products'])
                ->pluck('kode_produk')
                ->toArray();

            $invalidCodes = array_diff($data['applicable_products'], $existingCodes);
            if (!empty($invalidCodes)) {
                return $this->fail('Kode produk tidak valid: ' . implode(', ', $invalidCodes));
            }
        }

        $redeemCode = RedeemCode::create([
            'code'               => strtoupper($data['code']),
            'type'               => $data['type'] ?? RedeemCode::TYPE_DISCOUNT,
            'discount_value'     => $data['discount_value'] ?? 0,
            'custom_text'        => $data['custom_text'] ?? null,
            'applicable_products' => $data['applicable_products'] ?? null,
            'is_active'          => $data['is_active'] ?? true,
            'max_usage'          => $data['max_usage'] ?? 0,
            'valid_from'         => $data['valid_from'] ?? null,
            'valid_until'        => $data['valid_until'] ?? null,
        ]);

        return [
            'success' => true,
            'data'    => $redeemCode,
            'message' => 'Kode redeem berhasil dibuat!',
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
