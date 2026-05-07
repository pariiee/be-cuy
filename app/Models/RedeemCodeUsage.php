<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemCodeUsage extends Model
{
    protected $fillable = [
        'redeem_code_id',
        'user_id',
        'product_code',
        'discount_applied',
        'output_message',
    ];

    protected $casts = [
        'discount_applied' => 'integer',
    ];

    /**
     * Relationship: Usage belongs to a redeem code.
     */
    public function redeemCode(): BelongsTo
    {
        return $this->belongsTo(RedeemCode::class);
    }

    /**
     * Relationship: Usage belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
