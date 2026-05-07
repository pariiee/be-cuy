<?php

namespace App\Http\Controllers\Api;

use App\Models\QrisMarkupSetting;
use Illuminate\Http\Request;

class QrisMarkupController extends BaseApiController
{
    /**
     * GET /api/admin/qris-markup
     *
     * Get current QRIS markup settings.
     */
    public function show()
    {
        $setting = QrisMarkupSetting::current();

        return $this->success([
            'id' => $setting->id,
            'markup_deposit_type' => $setting->markup_deposit_type,
            'markup_deposit_value' => (float) $setting->markup_deposit_value,
            'markup_transaction_type' => $setting->markup_transaction_type,
            'markup_transaction_value' => (float) $setting->markup_transaction_value,
            'is_active' => $setting->is_active,
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ], 'Pengaturan markup QRIS');
    }

    /**
     * PUT /api/admin/qris-markup
     *
     * Update QRIS markup settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'markup_deposit_type' => 'required|in:fixed,percentage',
            'markup_deposit_value' => 'required|numeric|min:0',
            'markup_transaction_type' => 'required|in:fixed,percentage',
            'markup_transaction_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $setting = QrisMarkupSetting::current();
        $setting->update($validated);

        return $this->success([
            'id' => $setting->id,
            'markup_deposit_type' => $setting->markup_deposit_type,
            'markup_deposit_value' => (float) $setting->markup_deposit_value,
            'markup_transaction_type' => $setting->markup_transaction_type,
            'markup_transaction_value' => (float) $setting->markup_transaction_value,
            'is_active' => $setting->is_active,
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ], 'Markup QRIS berhasil diperbarui');
    }
}
