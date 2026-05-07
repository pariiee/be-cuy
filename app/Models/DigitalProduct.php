<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProduct extends Model
{
    protected $table = 'digital_products';

    protected $fillable = [
        'category_id',
        'nama_produk',
        'kode_produk',
        'app_category',
        'harga_user',
        'harga_reseller',
        'garansi',
        'deskripsi',
        'is_active',
        'stok',
        'sort_order',
    ];

    protected $casts = [
        'harga_user'     => 'integer',
        'harga_reseller' => 'integer',
        'garansi'        => 'boolean',
        'is_active'      => 'boolean',
        'stok'           => 'integer',
    ];

    /**
     * Relationship: Product belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DigitalProductCategory::class, 'category_id');
    }

    /**
     * Scope: Only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by category slug.
     */
    public function scopeByCategorySlug($query, string $slug)
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $slug));
    }



    /**
     * Scope: Only products with available stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stok', '>', 0);
    }

    /**
     * Check if product has available stock.
     */
    public function hasStock(): bool
    {
        return $this->stok > 0;
    }

    /**
     * Decrease stock by given amount. Returns false if insufficient stock.
     */
    public function decreaseStock(int $amount = 1): bool
    {
        if ($this->stok < $amount) {
            return false;
        }

        $this->decrement('stok', $amount);
        return true;
    }

    /**
     * Restock: increase stock by given amount.
     */
    public function restock(int $amount): void
    {
        $this->increment('stok', $amount);
    }

    /**
     * Get price based on user type.
     */
    public function getPriceFor(string $type = 'user'): int
    {
        return $type === 'reseller' ? $this->harga_reseller : $this->harga_user;
    }

    /**
     * Format product data for API response.
     */
    public function toApiArray(string $userType = 'user'): array
    {
        return [
            'id'             => $this->id,
            'nama_produk'    => $this->nama_produk,
            'kode_produk'    => $this->kode_produk,
            'kategori'       => 'PRODUCTV1',
            'app_category'   => $this->app_category,
            'harga_user'     => $this->harga_user,
            'harga_reseller' => $this->harga_reseller,
            'stok'           => $this->stok,
            'garansi'        => $this->garansi,
            'is_active'      => $this->is_active,
        ];
    }
}
