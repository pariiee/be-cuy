<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisFee extends Model
{
    protected $fillable = [
        'purpose',
        'fee_type',
        'fee_value',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'fee_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate fee amount for a given price.
     */
    public function calculateFee(float $amount): float
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($this->fee_type === 'percentage') {
            return round($amount * ($this->fee_value / 100), 2);
        }

        return (float) $this->fee_value;
    }

    /**
     * Get the active fee setting for a given purpose.
     *
     * @param string $purpose 'deposit' or 'transaction'
     */
    public static function getFor(string $purpose): ?self
    {
        return static::where('purpose', $purpose)
            ->where('is_active', true)
            ->first();
    }
}
