<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProduct extends Model
{
    protected $table = 'digital_products';

    protected $fillable = [
        'category_id',
        'nama_produk',
        'kode_produk',
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
        'garansi'        => 'integer',
        'is_active'      => 'boolean',
        'stok'           => 'integer',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(DigitalProductStock::class, 'product_id');
    }

    public function availableStocks(): HasMany
    {
        return $this->hasMany(DigitalProductStock::class, 'product_id')->where('is_sold', false);
    }

    /**
     * Sync stok cache and is_active from available stock items.
     */
    public function syncStok(): void
    {
        $count = $this->stocks()->where('is_sold', false)->count();
        $this->update([
            'stok'      => $count,
            'is_active' => $count > 0,
        ]);
    }

    /**
     * Add multiple stock items from an array of content strings.
     */
    public function addStockItems(array $lines): int
    {
        $items = array_filter(array_map('trim', $lines));
        foreach ($items as $content) {
            $this->stocks()->create(['content' => $content]);
        }
        $this->syncStok();
        return count($items);
    }

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
     * Legacy restock kept for compatibility — prefer addStockItems().
     */
    public function restock(int $amount): void
    {
        $this->increment('stok', $amount);
        $this->update(['is_active' => true]);
    }

    /**
     * Grab one available stock item, mark as sold, return it.
     * Returns null if no stock available.
     */
    public function grabStockItem(?int $userId = null, ?string $orderRef = null): ?DigitalProductStock
    {
        $item = $this->stocks()
            ->where('is_sold', false)
            ->lockForUpdate()
            ->first();

        if (! $item) {
            return null;
        }

        $item->update([
            'is_sold'          => true,
            'sold_at'          => now(),
            'sold_to_user_id'  => $userId,
            'order_ref'        => $orderRef,
        ]);

        $this->syncStok();

        return $item;
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
            'harga_user'     => $this->harga_user,
            'harga_reseller' => $this->harga_reseller,
            'stok'           => $this->stok,
            'garansi'        => $this->garansi,
            'is_active'      => $this->is_active,
        ];
    }
}
