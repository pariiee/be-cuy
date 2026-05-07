<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'order_ref',
        'product_code',
        'product_name',
        'category',
        'target',
        'quantity',
        'base_price',
        'markup',
        'sell_price',
        'profit',
        'payment_method',
        'payment_fee',
        'total_pay',
        'payment_status',
        'status',
        'provider_response',
        'notes',
        'sn',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'markup' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'payment_fee' => 'decimal:2',
        'total_pay' => 'decimal:2',
        'provider_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deposit(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Deposit::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
