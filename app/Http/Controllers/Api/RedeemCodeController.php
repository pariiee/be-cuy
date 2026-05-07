<?php

namespace App\Http\Controllers\Api;

use App\Services\RedeemCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedeemCodeController extends BaseApiController
{
    public function __construct(
        protected RedeemCodeService $redeemService
    ) {}

    /**
     * POST /api/digital/redeem/validate
     *
     * Validate a redeem code without applying it.
     * Body: { "code": "DISKONHEMAT", "product_code": "IG-FOL-1K" }
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => 'required|string|max:50',
            'product_code' => 'nullable|string|max:50',
        ]);

        $result = $this->redeemService->validate(
            $request->input('code'),
            $request->input('product_code')
        );

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['data'], $result['message']);
    }

    /**
     * POST /api/digital/redeem/apply
     *
     * Apply a redeem code to a product for the authenticated user.
     * Body: { "code": "DISKONHEMAT", "product_code": "IG-FOL-1K", "price": 50000 }
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => 'required|string|max:50',
            'product_code' => 'required|string|max:50',
            'price'        => 'required|integer|min:0',
        ]);

        $result = $this->redeemService->apply(
            $request->input('code'),
            $request->input('product_code'),
            $request->user()->id,
            $request->input('price')
        );

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['data'], $result['message']);
    }
}
