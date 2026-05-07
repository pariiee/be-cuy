<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'invoice_number',
        'amount',
        'method',
        'purpose',
        'status',
        'qris_content',
        'qris_image_url',
        'qris_invoiceid',
        'qris_nmid',
        'qris_request_date',
        'qris_expired_at',
        'payinaja_trx_id',
        'payinaja_fee',
        'payinaja_total',
        'payment_customer_name',
        'payment_method_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payinaja_fee' => 'decimal:2',
        'payinaja_total' => 'decimal:2',
        'qris_request_date' => 'datetime',
        'qris_expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->qris_expired_at && now()->greaterThan($this->qris_expired_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
