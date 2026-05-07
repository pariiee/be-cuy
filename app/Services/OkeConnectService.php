<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OkeConnectService
{
    protected string $baseUrl;
    protected string $memberId;
    protected string $pin;
    protected string $password;
    protected string $priceApiId;

    public function __construct()
    {
        $this->baseUrl    = config('services.okeconnect.base_url', 'https://h2h.okeconnect.com');
        $this->memberId   = config('services.okeconnect.member_id', '');
        $this->pin        = config('services.okeconnect.pin', '');
        $this->password   = config('services.okeconnect.password', '');
        $this->priceApiId = config('services.okeconnect.price_api_id', '');
    }

    /**
     * Parse OkeConnect plain-text response into a structured array.
     *
     * OkeConnect returns plain text, e.g.:
     *   "T#762261897 R#7777 H2H DANA ... akan diproses. Saldo 43.928.256 - 12.516 = 43.915.740 @19:14"
     *   "T#41168891 R#1234 Telkomsel 5.000 S5.082280004280 SUKSES. SN/Ref: R210630... Saldo ..."
     *   "T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo ..."
     */
    public function parseResponse(string $text): array
    {
        $text = trim($text);
        $upper = strtoupper($text);

        if (str_contains($upper, 'SUKSES')) {
            $status = 'success';
        } elseif (str_contains($upper, 'GAGAL')) {
            $status = 'failed';
        } elseif (str_contains($upper, 'AKAN DIPROSES') || str_contains($upper, 'MENUNGGU JAWABAN')) {
            $status = 'processing';
        } elseif (str_contains($upper, 'PENDING') || str_contains($upper, 'MENUNGGU')) {
            $status = 'pending';
        } elseif (str_contains($upper, 'IP TIDAK SESUAI') || str_contains($upper, 'IP SALAH')) {
            $status = 'error_ip';
        } else {
            $status = 'processing';
        }

        // Extract SN/Ref number
        $sn = null;
        if (preg_match('/SN[\/\s:]+([A-Za-z0-9.]+)/i', $text, $m)) {
            $sn = $m[1];
        }

        // Extract trx number (T#...)
        $trxId = null;
        if (preg_match('/T#(\d+)/i', $text, $m)) {
            $trxId = $m[1];
        }

        return [
            'raw'    => $text,
            'status' => $status,
            'sn'     => $sn,
            'trx_id' => $trxId,
        ];
    }

    /**
     * Get product list by category from OkeConnect price API.
     *
     * @param string $category e.g. 'pulsa', 'saldo_gojek', 'token_pln'
     * @return array|null
     */
    public function getProducts(string $category): ?array
    {
        try {
            $response = Http::get('https://okeconnect.com/harga/json', [
                'id'     => $this->priceApiId,
                'produk' => $category,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OkeConnect getProducts failed', [
                'category' => $category,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OkeConnect getProducts exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Submit a regular (fixed-price) top-up transaction.
     * OkeConnect returns plain text, NOT JSON.
     *
     * @param string $productCode e.g. 'S10', 'T5'
     * @param string $destination Phone number or customer ID
     * @param string $refId       Unique reference ID
     * @return array|null  Parsed response array, or null on HTTP/network failure
     */
    public function createTransaction(string $productCode, string $destination, string $refId): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/trx', [
                'product'  => $productCode,
                'dest'     => $destination,
                'refID'    => $refId,
                'memberID' => $this->memberId,
                'pin'      => $this->pin,
                'password' => $this->password,
            ]);

            $body = $response->body();

            Log::info('OkeConnect createTransaction response', [
                'product' => $productCode,
                'refId'   => $refId,
                'http'    => $response->status(),
                'body'    => $body,
            ]);

            if (!$response->successful()) {
                Log::error('OkeConnect createTransaction HTTP error', [
                    'product' => $productCode,
                    'refId'   => $refId,
                    'status'  => $response->status(),
                ]);
                return null;
            }

            return $this->parseResponse($body);
        } catch (\Exception $e) {
            Log::error('OkeConnect createTransaction exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Submit a nominal-bebas (open-denom) e-wallet top-up.
     * OkeConnect uses the 'qty' parameter for the amount.
     * OkeConnect returns plain text, NOT JSON.
     *
     * @param string $productCode e.g. 'BBSGOP', 'BBSD', 'BBSOVON'
     * @param string $destination Phone number linked to the e-wallet
     * @param int    $qty         Top-up amount in IDR (min 10000, max 10000000)
     * @param string $refId       Unique reference ID
     * @return array|null  Parsed response array, or null on HTTP/network failure
     */
    public function createNominalBebasTransaction(string $productCode, string $destination, int $qty, string $refId): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/trx', [
                'product'  => $productCode,
                'dest'     => $destination,
                'qty'      => $qty,
                'refID'    => $refId,
                'memberID' => $this->memberId,
                'pin'      => $this->pin,
                'password' => $this->password,
            ]);

            $body = $response->body();

            Log::info('OkeConnect createNominalBebas response', [
                'product' => $productCode,
                'qty'     => $qty,
                'refId'   => $refId,
                'http'    => $response->status(),
                'body'    => $body,
            ]);

            if (!$response->successful()) {
                Log::error('OkeConnect createNominalBebas HTTP error', [
                    'product' => $productCode,
                    'refId'   => $refId,
                    'status'  => $response->status(),
                ]);
                return null;
            }

            return $this->parseResponse($body);
        } catch (\Exception $e) {
            Log::error('OkeConnect createNominalBebas exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check status of an existing transaction using check=1.
     *
     * @param string      $productCode
     * @param string      $destination
     * @param string      $refId
     * @param int|null    $qty  Required for open-denom products
     * @return array|null
     */
    public function checkTransactionStatus(string $productCode, string $destination, string $refId, ?int $qty = null): ?array
    {
        try {
            $params = [
                'product'  => $productCode,
                'dest'     => $destination,
                'refID'    => $refId,
                'memberID' => $this->memberId,
                'pin'      => $this->pin,
                'password' => $this->password,
                'check'    => 1,
            ];

            if ($qty !== null) {
                $params['qty'] = $qty;
            }

            $response = Http::get($this->baseUrl . '/trx', $params);
            $body     = $response->body();

            Log::info('OkeConnect checkStatus response', [
                'product' => $productCode,
                'refId'   => $refId,
                'body'    => $body,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $parsed = $this->parseResponse($body);

            // Enrich status from check-specific phrases
            $upper = strtoupper($body);
            if (str_contains($upper, 'TIDAK ADA') || str_contains($upper, 'NO DATA')) {
                $parsed['status'] = 'not_found';
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('OkeConnect checkStatus exception', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
