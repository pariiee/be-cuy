<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QrisService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.payinaja.base_url', 'https://payinaja.web.id/api/v1'), '/');
        $this->apiKey = config('services.payinaja.api_key', '');
    }

    /**
     * Create a QRIS payment via PayinAja.
     *
     * @param string $referenceId Our transaction/invoice reference
     * @param int    $amount      Amount in IDR (min 100). Fee 0.7% applied automatically by PayinAja.
     * @param string|null $customerName Optional customer name for record keeping
     * @return array|null Returns PayinAja response data or null on failure
     *
     * Success response shape:
     * [
     *   'payinaja_trx_id'  => 'TRX-...',
     *   'merchant_ref'     => 'INV-001',
     *   'amount_requested' => 15000,
     *   'fee'              => 105,
     *   'total_amount'     => 15105,
     *   'qris_string'      => '000201010212...',
     *   'qris_image_url'   => 'https://quickchart.io/qr?...',
     *   'status'           => 'pending',
     * ]
     */
    public function createQris(string $referenceId, int $amount, ?string $customerName = null): ?array
    {
        try {
            $payload = [
                'amount' => $amount,
                'reference_id' => $referenceId,
            ];

            if ($customerName) {
                $payload['customer_name'] = $customerName;
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/qris/create", $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (($data['success'] ?? false) === true && isset($data['data'])) {
                    return $data['data'];
                }

                Log::error('PayinAja createQris failed', [
                    'reference' => $referenceId,
                    'response' => $data,
                ]);

                return null;
            }

            Log::error('PayinAja createQris HTTP error', [
                'reference' => $referenceId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayinAja createQris exception', [
                'reference' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check transaction status via PayinAja.
     *
     * @param string $trxId The payinaja_trx_id from create response
     * @return array|null Returns transaction data or null on error
     *
     * Success response shape:
     * [
     *   'trx_id'         => 'TRX-...',
     *   'merchant_ref'   => 'INV-001',
     *   'status'         => 'success' | 'pending' | 'failed',
     *   'net_amount'     => 15000,
     *   'fee'            => 105,
     *   'total_amount'   => 15105,
     *   'payment_method' => 'QRIS',
     *   'created_at'     => '2026-03-30T11:29:35+00:00',
     * ]
     */
    public function checkStatus(string $trxId): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->baseUrl}/transaction/{$trxId}");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['success'] ?? false) === true && isset($data['data'])) {
                    return $data['data'];
                }

                Log::warning('PayinAja checkStatus unexpected response', [
                    'trxId' => $trxId,
                    'response' => $data,
                ]);

                return null;
            }

            Log::error('PayinAja checkStatus HTTP error', [
                'trxId' => $trxId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayinAja checkStatus exception', [
                'trxId' => $trxId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get merchant profile & balance from PayinAja.
     *
     * @return array|null
     */
    public function getProfile(): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->baseUrl}/profile");

            if ($response->successful()) {
                $data = $response->json();

                if (($data['success'] ?? false) === true && isset($data['data'])) {
                    return $data['data'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayinAja getProfile exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
