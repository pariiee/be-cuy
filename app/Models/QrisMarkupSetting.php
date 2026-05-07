<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrisMarkupSetting extends Model
{
    protected $fillable = [
        'markup_deposit_type',
        'markup_deposit_value',
        'markup_transaction_type',
        'markup_transaction_value',
        'is_active',
    ];

    protected $casts = [
        'markup_deposit_value' => 'decimal:2',
        'markup_transaction_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the singleton settings row (single-row config table).
     */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'markup_deposit_type' => 'fixed',
            'markup_deposit_value' => 0,
            'markup_transaction_type' => 'fixed',
            'markup_transaction_value' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Calculate markup for a given amount and purpose.
     *
     * @param float  $amount  Base amount
     * @param string $purpose 'deposit' or 'transaction'
     */
    public function calculateMarkup(float $amount, string $purpose = 'transaction'): float
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($purpose === 'deposit') {
            $type = $this->markup_deposit_type;
            $value = (float) $this->markup_deposit_value;
        } else {
            $type = $this->markup_transaction_type;
            $value = (float) $this->markup_transaction_value;
        }

        if ($value <= 0) {
            return 0;
        }

        if ($type === 'percentage') {
            return round($amount * ($value / 100), 2);
        }

        return $value;
    }
}
