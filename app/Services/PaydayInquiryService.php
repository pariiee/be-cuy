<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaydayInquiryService
{
    protected string $baseUrl;
    protected string $apiKey;

    /**
     * Product mapping: slug => [path, label, param, category, extra_params]
     *
     * - path: URL path after /trueid/
     * - label: Human-readable label
     * - param: Main query parameter name for the target ID
     * - category: game | ewallet | bill | bank
     * - extra: Optional extra required params (e.g. area for PDAM, kode for bank)
     */
    public const PRODUCTS = [
        // ─── Gaming ────────────────────────────────────────────
        'freefire'       => ['path' => 'game/freefire',       'label' => 'Free Fire',          'param' => 'id', 'category' => 'game'],
        'mobilelegends'  => ['path' => 'game/mobilelegends',  'label' => 'Mobile Legends',     'param' => 'id', 'category' => 'game'],
        'aov'            => ['path' => 'game/aov',            'label' => 'Arena of Valor',     'param' => 'id', 'category' => 'game'],
        'codm'           => ['path' => 'game/codm',           'label' => 'Call of Duty Mobile','param' => 'id', 'category' => 'game'],
        'valorant'       => ['path' => 'game/valorant',       'label' => 'Valorant',           'param' => 'id', 'category' => 'game'],
        'pubg'           => ['path' => 'game/pubg',           'label' => 'PUBG Mobile',        'param' => 'id', 'category' => 'game'],
        'tom-jerry'      => ['path' => 'game/tom-jerry',      'label' => 'Tom and Jerry',      'param' => 'id', 'category' => 'game'],
        'undawn'         => ['path' => 'game/undawn',          'label' => 'Undawn',             'param' => 'id', 'category' => 'game'],
        'zepeto'         => ['path' => 'game/zepeto',          'label' => 'Zepeto',             'param' => 'id', 'category' => 'game'],

        // ─── E-Wallet ──────────────────────────────────────────
        'gopay'         => ['path' => 'ewallet/gopay',         'label' => 'GoPay',        'param' => 'hp', 'category' => 'ewallet'],
        'linkaja'       => ['path' => 'ewallet/linkaja',       'label' => 'LinkAja',      'param' => 'hp', 'category' => 'ewallet'],
        'gopay_driver'  => ['path' => 'ewallet/gopay_driver',  'label' => 'GoPay Driver', 'param' => 'hp', 'category' => 'ewallet'],
        'dana'          => ['path' => 'ewallet/dana',          'label' => 'DANA',         'param' => 'hp', 'category' => 'ewallet'],
        'ovo'           => ['path' => 'ewallet/ovo',           'label' => 'OVO',          'param' => 'hp', 'category' => 'ewallet'],
        'shopeepay'     => ['path' => 'ewallet/shopeepay',     'label' => 'ShopeePay',    'param' => 'hp', 'category' => 'ewallet'],

        // ─── Bill / Tagihan ────────────────────────────────────
        'my_republic'   => ['path' => 'bill/my_republic', 'label' => 'My Republic',     'param' => 'no', 'category' => 'bill'],
        'telkom'        => ['path' => 'bill/telkom',       'label' => 'Tagihan Telkom',  'param' => 'no', 'category' => 'bill'],
        'pdam'          => ['path' => 'bill/pdam',         'label' => 'PDAM',            'param' => 'no', 'category' => 'bill', 'extra' => ['area']],
        'pln'           => ['path' => 'bill/pln',          'label' => 'Token PLN',       'param' => 'no', 'category' => 'bill'],

        // ─── Bank ──────────────────────────────────────────────
        'bank'          => ['path' => 'bank',    'label' => 'Cek Rekening Bank',              'param' => 'norek', 'category' => 'bank', 'extra' => ['kode']],
        'bank_s2'       => ['path' => 'bank_S2', 'label' => 'Cek Rekening Bank (Server 2)',   'param' => 'norek', 'category' => 'bank', 'extra' => ['kode']],
    ];

    /**
     * Bank codes for server 1 (numeric code).
     */
    public const BANK_CODES = [
        '014' => 'Bank Central Asia (BCA)',
        '008' => 'Bank Mandiri',
        '002' => 'Bank Rakyat Indonesia (BRI)',
        '009' => 'Bank Negara Indonesia (BNI)',
        '451' => 'BSI (Bank Syariah Indonesia)',
        '022' => 'CIMB Niaga & CIMB Niaga Syariah',
        '535' => 'Seabank / Bank BKE',
        '542' => 'Bank Jago',
        '947' => 'Bank Aladin Syariah',
        '501' => 'Bank Blu / BCA Digital',
        '484' => 'LINE Bank / KEB Hana',
        '490' => 'Neo Commerce (BNC) / Yudha Bhakti',
        '503' => 'Nobu Bank',
        '566' => 'Superbank',
        '023' => 'TMRW / UOB',
        '441' => 'Wokee / Bukopin',
        '521' => 'Bank Bukopin Syariah',
        '536' => 'BCA Syariah',
        '200' => 'BTN',
        '422' => 'BTN Syariah',
        '213' => 'BTPN',
        '547' => 'Bank BTPN Syariah',
        '031' => 'Citibank',
        '011' => 'Bank Danamon',
        '472' => 'Bank Jasa Jakarta',
        '097' => 'Bank Mayapada',
        '426' => 'Bank Mega',
        '506' => 'Bank Mega Syariah',
        '145' => 'Bank Nusantara Parahyangan',
        '028' => 'Bank OCBC NISP',
        '019' => 'Panin Bank',
        '013' => 'Permata Bank',
        '784' => 'Permata Syariah',
        '129' => 'BPD Bali',
        '137' => 'BPD Banten',
        '133' => 'Bank Bengkulu',
        '110' => 'BJB',
        '425' => 'BJB Syariah',
        '112' => 'Bank BPD DIY',
        '111' => 'Bank DKI',
        '115' => 'Bank Jambi',
        '113' => 'Bank Jateng',
        '114' => 'Bank Jatim',
        '123' => 'Bank Kalbar',
        '122' => 'Bank Kalsel',
        '125' => 'Bank Kalteng',
        '124' => 'Bank Kaltimtara',
        '121' => 'Bank Lampung',
        '131' => 'Bank Maluku',
        '118' => 'Bank Nagari (Sumbar)',
        '128' => 'Bank NTB Syariah',
        '130' => 'Bank NTT',
        '132' => 'Bank Papua',
        '119' => 'Bank Riau Kepri',
        '126' => 'Bank Sulselbar',
        '134' => 'Bank Sulteng',
        '135' => 'Bank Sultra',
        '127' => 'Bank SulutGo',
        '120' => 'Bank Sumsel Babel',
        '117' => 'Bank Sumut',
    ];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.payday.base_url', 'https://api.payday.my.id'), '/');
        $this->apiKey  = config('services.payday.key') ?? '';
    }

    /**
     * Check if a product slug is valid.
     */
    public function isValidProduct(string $product): bool
    {
        return isset(self::PRODUCTS[$product]);
    }

    /**
     * Get all supported products grouped by category.
     */
    public function getSupportedProducts(): array
    {
        $grouped = [];
        foreach (self::PRODUCTS as $slug => $info) {
            $grouped[$info['category']][$slug] = [
                'label' => $info['label'],
                'param' => $info['param'],
                'extra' => $info['extra'] ?? [],
            ];
        }
        return $grouped;
    }

    /**
     * Get bank codes for a given server.
     */
    public function getBankCodes(string $server = 'bank'): array
    {
        return self::BANK_CODES;
    }

    /**
     * Send inquiry request to Payday TrueID API.
     *
     * @param string $product   Product slug (e.g. 'gopay', 'tom-jerry', 'bank')
     * @param string $targetId  The user/account ID, phone number, or account number
     * @param array  $extra     Extra parameters (e.g. ['area' => 'kota_surabaya'] for PDAM, ['kode' => '014'] for bank)
     * @return array{success: bool, data: array|null, message: string}
     */
    public function inquiry(string $product, string $targetId, array $extra = []): array
    {
        $product = strtolower(trim($product));

        if (!$this->isValidProduct($product)) {
            return [
                'success' => false,
                'data' => null,
                'message' => "Produk '{$product}' tidak tersedia untuk inquiry.",
            ];
        }

        $config = self::PRODUCTS[$product];
        $requiredExtra = $config['extra'] ?? [];

        // Validate required extra params
        foreach ($requiredExtra as $param) {
            if (empty($extra[$param])) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => "Parameter '{$param}' wajib diisi untuk produk {$config['label']}.",
                ];
            }
        }

        try {
            // Build query params
            $query = [
                $config['param'] => $targetId,
                'key' => $this->apiKey,
            ];

            // Add extra params
            foreach ($requiredExtra as $param) {
                $query[$param] = $extra[$param];
            }

            $url = $this->baseUrl . '/trueid/' . $config['path'] . '/';

            $response = Http::timeout(15)->get($url, $query);

            if (!$response->successful()) {
                Log::error('Payday inquiry HTTP error', [
                    'product' => $product,
                    'target' => $targetId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Gagal menghubungi server Payday (HTTP ' . $response->status() . ')',
                ];
            }

            $body = trim($response->body());
            $json = $response->json();

            // Parse JSON response
            if (is_array($json) && !empty($json)) {
                return $this->parseResponse($json, $product, $targetId, $config);
            }

            // Plain text fallback
            if (!empty($body)) {
                return [
                    'success' => false,
                    'data' => [
                        'product' => $product,
                        'target_id' => $targetId,
                        'raw_response' => $body,
                    ],
                    'message' => $body,
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Tidak ada respon dari server.',
            ];

        } catch (\Exception $e) {
            Log::error('Payday inquiry exception', [
                'product' => $product,
                'target' => $targetId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse JSON response from Payday API.
     */
    protected function parseResponse(array $json, string $product, string $targetId, array $config): array
    {
        // Check for error responses
        $status = $json['status'] ?? $json['success'] ?? null;
        $isError = $status === false || $status === 'error' || $status === 'false';

        if ($isError) {
            return [
                'success' => false,
                'data' => [
                    'product' => $product,
                    'target_id' => $targetId,
                    'raw_response' => $json,
                ],
                'message' => $json['message'] ?? $json['msg'] ?? $json['error'] ?? 'Akun tidak ditemukan.',
            ];
        }

        // Extract account name from common response fields
        $accountName = $json['nickname'] ?? $json['result'] ?? $json['name'] ?? $json['nama']
            ?? $json['customer_name'] ?? $json['username']
            ?? $json['data']['nickname'] ?? $json['data']['name'] ?? $json['data']['nama']
            ?? $json['data']['customer_name'] ?? $json['data']['username']
            ?? null;

        // If result is an array, try to find name inside it
        if (is_array($accountName)) {
            $accountName = $accountName['name'] ?? $accountName['nama']
                ?? $accountName['customer_name'] ?? $accountName['username']
                ?? json_encode($accountName);
        }

        return [
            'success' => true,
            'data' => [
                'product' => $product,
                'target_id' => $targetId,
                'label' => $config['label'],
                'category' => $config['category'],
                'account_name' => $accountName,
                'raw_response' => $json,
            ],
            'message' => $accountName
                ? "Akun ditemukan: {$accountName}"
                : 'Inquiry berhasil, namun nama akun tidak tersedia.',
        ];
    }
}
