<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProductStock extends Model
{
    protected $table = 'digital_product_stocks';

    protected $fillable = [
        'product_id',
        'content',
        'is_sold',
        'sold_at',
        'sold_to_user_id',
        'order_ref',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'sold_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'product_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    protected static function booted(): void
    {
        static::deleted(function (DigitalProductStock $stock) {
            $stock->product?->syncStok();
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_sold', false);
    }

    public function scopeSold($query)
    {
        return $query->where('is_sold', true);
    }
}
