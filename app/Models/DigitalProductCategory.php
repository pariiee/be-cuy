<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProductCategory extends Model
{
    protected $table = 'digital_product_categories';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Category has many products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(DigitalProduct::class, 'category_id');
    }

    /**
     * Scope: Only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('nama_kategori');
    }
}
