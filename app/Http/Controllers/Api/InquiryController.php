<?php

namespace App\Http\Controllers\Api;

use App\Services\PaydayInquiryService;
use Illuminate\Http\Request;

class InquiryController extends BaseApiController
{
    public function __construct(
        protected PaydayInquiryService $inquiryService
    ) {}

    /**
     * GET /api/inquiry/products
     *
     * List all supported inquiry products grouped by category.
     */
    public function products()
    {
        return $this->success(
            $this->inquiryService->getSupportedProducts(),
            'Daftar produk inquiry yang didukung'
        );
    }

    /**
     * GET /api/inquiry/bank-codes
     *
     * List all supported bank codes for bank account inquiry.
     */
    public function bankCodes()
    {
        return $this->success(
            $this->inquiryService->getBankCodes(),
            'Daftar kode bank'
        );
    }

    /**
     * POST /api/inquiry/check
     *
     * Send an inquiry (cek nama/ID) request to Payday TrueID API.
     *
     * Body: {
     *   "product": "gopay",
     *   "target_id": "081234567890",
     *   "extra": { "kode": "014" }   // optional, for bank/pdam
     * }
     *
     * Returns account name / owner info in real-time.
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string|max:50',
            'target_id' => 'required|string|max:100',
            'extra' => 'nullable|array',
            'extra.*' => 'string|max:100',
        ]);

        $result = $this->inquiryService->inquiry(
            $validated['product'],
            $validated['target_id'],
            $validated['extra'] ?? []
        );

        if (!$result['success']) {
            return $this->error(
                $result['message'],
                422,
                $result['data']
            );
        }

        return $this->success($result['data'], $result['message']);
    }
}
