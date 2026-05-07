<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedeemCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_value',
        'custom_text',
        'applicable_products',
        'is_active',
        'max_usage',
        'used_count',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'applicable_products' => 'array',
        'is_active'           => 'boolean',
        'max_usage'           => 'integer',
        'used_count'          => 'integer',
        'discount_value'      => 'integer',
        'valid_from'          => 'datetime',
        'valid_until'         => 'datetime',
    ];

    /**
     * Type constants.
     */
    const TYPE_DISCOUNT    = 'discount';
    const TYPE_CUSTOM_TEXT = 'custom_text';

    /**
     * Max products per redeem code.
     */
    const MAX_APPLICABLE_PRODUCTS = 5;

    /**
     * Relationship: usage history.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(RedeemCodeUsage::class);
    }

    /**
     * Check if code is still valid for use.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_usage > 0 && $this->used_count >= $this->max_usage) {
            return false;
        }

        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && now()->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if code is applicable to a specific product.
     */
    public function isApplicableTo(string $productCode): bool
    {
        // If no specific products set, applies to all
        if (empty($this->applicable_products)) {
            return true;
        }

        return in_array($productCode, $this->applicable_products);
    }

    /**
     * Get the output for this redeem code.
     */
    public function getOutput(int $originalPrice = 0): array
    {
        if ($this->type === self::TYPE_DISCOUNT) {
            $discountedPrice = max(0, $originalPrice - $this->discount_value);
            return [
                'type'             => 'discount',
                'discount_value'   => $this->discount_value,
                'original_price'   => $originalPrice,
                'discounted_price' => $discountedPrice,
                'message'          => "Diskon Rp " . number_format($this->discount_value, 0, ',', '.') . " berhasil diterapkan!",
            ];
        }

        return [
            'type'    => 'custom_text',
            'message' => $this->custom_text ?? 'Kode redeem berhasil digunakan!',
        ];
    }

    /**
     * Increment usage counter.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Scope: Only active and valid codes.
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('max_usage')
                    ->orWhere('max_usage', 0)
                    ->orWhereColumn('used_count', '<', 'max_usage');
            })
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }
}
