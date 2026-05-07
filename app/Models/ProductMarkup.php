<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMarkup extends Model
{
    protected $fillable = [
        'provider',
        'product_code',
        'category',
        'markup_type',
        'markup_value',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'markup_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate markup amount for a given base price.
     */
    public function calculateMarkup(float $basePrice): float
    {
        if ($this->markup_type === 'percentage') {
            return round($basePrice * ($this->markup_value / 100), 2);
        }

        return (float) $this->markup_value;
    }

    /**
     * Get the sell price (base + markup).
     */
    public function getSellPrice(float $basePrice): float
    {
        return $basePrice + $this->calculateMarkup($basePrice);
    }

    /**
     * Find the best matching markup for a product.
     * Priority: specific product_code > category > global
     */
    public static function findMarkup(string $provider, ?string $productCode = null, ?string $category = null): ?self
    {
        // 1. Try exact product code match
        if ($productCode) {
            $markup = static::where('provider', $provider)
                ->where('product_code', $productCode)
                ->where('is_active', true)
                ->first();

            if ($markup) return $markup;
        }

        // 2. Try category match
        if ($category) {
            $markup = static::where('provider', $provider)
                ->whereNull('product_code')
                ->where('category', $category)
                ->where('is_active', true)
                ->first();

            if ($markup) return $markup;
        }

        // 3. Try global provider match
        return static::where('provider', $provider)
            ->whereNull('product_code')
            ->whereNull('category')
            ->where('is_active', true)
            ->first();
    }
}
