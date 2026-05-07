<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\ProductMarkup;
use App\Models\User;
use App\Services\OkeConnectService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EWalletController extends BaseApiController
{
    /**
     * Nominal bebas e-wallet products from OkeConnect.
     * base_fee = OkeConnect's fixed transaction fee per top-up (IDR).
     */
    protected static array $PRODUCTS = [
        ['code' => 'BBSGOP',  'name' => 'GoPay',        'provider' => 'GoPay',     'base_fee' => 850],
        ['code' => 'BBSGOD',  'name' => 'GoPay Driver',  'provider' => 'GoPay',     'base_fee' => 450],
        ['code' => 'BBSOVON', 'name' => 'OVO',           'provider' => 'OVO',       'base_fee' => 680],
        ['code' => 'BBSD',    'name' => 'Dana',          'provider' => 'Dana',      'base_fee' => 48],
        ['code' => 'BBSSH',   'name' => 'ShopeePay',     'provider' => 'ShopeePay', 'base_fee' => 500],
        ['code' => 'BBSTC',   'name' => 'LinkAja',       'provider' => 'LinkAja',   'base_fee' => 0],
        ['code' => 'BBSASTR', 'name' => 'AstraPay',      'provider' => 'AstraPay',  'base_fee' => 800],
        ['code' => 'BBSDOKU', 'name' => 'DOKU',          'provider' => 'DOKU',      'base_fee' => 800],
        ['code' => 'BBSAKU',  'name' => 'iSaku',         'provider' => 'iSaku',     'base_fee' => 800],
    ];

    public function __construct(
        protected OkeConnectService $okeConnect,
        protected PaymentService $payment
    ) {}

    /**
     * GET /api/ewallet/options
     *
     * List all available nominal-bebas e-wallet providers with fee info.
     */
    public function options()
    {
        $result = collect(self::$PRODUCTS)->map(function ($product) {
            $markup = ProductMarkup::findMarkup('okeconnect', $product['code'], 'ewallet');
            $adminFee = $markup ? (int) $markup->calculateMarkup($product['base_fee']) : 0;

            return [
                'code'        => $product['code'],
                'name'        => $product['name'],
                'provider'    => $product['provider'],
                'base_fee'    => $product['base_fee'],
                'admin_fee'   => $adminFee,
                'total_fee'   => $product['base_fee'] + $adminFee,
                'min_nominal' => 10000,
                'max_nominal' => 1000000,
                'note'        => 'Harga = nominal + Rp ' . number_format($product['base_fee'] + $adminFee, 0, ',', '.') . ' (biaya layanan)',
            ];
        });

        return $this->success($result->values());
    }

    /**
     * POST /api/ewallet/topup
     *
     * Submit a nominal-bebas e-wallet top-up.
     *
     * Body: {
     *   "product_code": "BBSGOP",
     *   "destination":  "08123456789",
     *   "nominal":      50000,
     *   "payment_method": "balance"  // "balance" or "qris"
     * }
     */
    public function topup(Request $request)
    {
        $validated = $request->validate([
            'product_code'   => 'required|string',
            'destination'    => ['required', 'string', 'max:50', 'regex:/^[0-9]{6,20}$/'],
            'nominal'        => 'required|integer|min:10000|max:1000000',
            'payment_method' => 'required|in:balance,qris',
        ], [
            'destination.regex' => 'Nomor tujuan harus berupa angka (6-20 digit).',
        ]);

        $product = collect(self::$PRODUCTS)->firstWhere('code', $validated['product_code']);
        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $nominal  = (int) $validated['nominal'];
        $baseFee  = (int) $product['base_fee'];
        $user     = $request->user();

        // Admin & Reseller bypass admin fee markup
        $adminFee = 0;
        if (!$user->isExemptFromMarkup()) {
            $markup   = ProductMarkup::findMarkup('okeconnect', $validated['product_code'], 'ewallet');
            $adminFee = $markup ? (int) $markup->calculateMarkup($baseFee) : 0;
        }
        $totalFee      = $baseFee + $adminFee;
        $sellPrice     = $nominal + $totalFee;
        $paymentMethod = $validated['payment_method'];
        $paymentFee    = 0;

        if ($paymentMethod === 'qris') {
            if (!$this->payment->isQrisEnabled()) {
                return $this->error('Pembayaran QRIS sedang tidak tersedia', 422);
            }
            $paymentFee = $this->payment->calculateQrisFee($sellPrice, 'transaction', $user);
        }

        $totalPay = $sellPrice + $paymentFee;
        $refId    = 'EW-' . time() . '-' . Str::random(6);

        if ($paymentMethod === 'balance') {
            return $this->processWithBalance($user, $validated, $product, $nominal, $baseFee, $adminFee, $sellPrice, $refId);
        }

        return $this->processWithQris($user, $validated, $product, $nominal, $baseFee, $adminFee, $sellPrice, $paymentFee, $totalPay, $refId);
    }

    private function processWithBalance($user, array $v, array $product, int $nominal, int $baseFee, int $adminFee, float $sellPrice, string $refId)
    {
        if ($user->balance < $sellPrice) {
            return $this->error(
                'Saldo tidak cukup. Dibutuhkan Rp' . number_format($sellPrice, 0, ',', '.') .
                ', saldo Anda Rp' . number_format($user->balance, 0, ',', '.'), 422
            );
        }

        try {
        $order = DB::transaction(function () use ($user, $v, $product, $nominal, $baseFee, $adminFee, $sellPrice, $refId) {
            $lockedUser = User::lockForUpdate()->find($user->id);
            if ($lockedUser->balance < $sellPrice) {
                throw new \Exception('INSUFFICIENT_BALANCE');
            }
            $lockedUser->decrement('balance', $sellPrice);

            return Order::create([
                'user_id'        => $user->id,
                'provider'       => 'okeconnect',
                'order_ref'      => $refId,
                'product_code'   => $v['product_code'],
                'product_name'   => $product['name'] . ' Rp' . number_format($nominal, 0, ',', '.'),
                'category'       => 'ewallet',
                'target'         => $v['destination'],
                'quantity'       => 1,
                'base_price'     => $nominal,
                'markup'         => $adminFee,
                'sell_price'     => $sellPrice,
                'profit'         => $adminFee,
                'payment_method' => 'balance',
                'payment_fee'    => 0,
                'total_pay'      => $sellPrice,
                'payment_status' => 'lunas',
                'status'         => 'processing',
            ]);
        });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                return $this->error('Saldo tidak cukup. Dibutuhkan Rp' . number_format($sellPrice, 0, ',', '.'), 422);
            }
            throw $e;
        }

        $result = $this->okeConnect->createNominalBebasTransaction(
            $v['product_code'],
            $v['destination'],
            $nominal,
            $refId
        );

        if ($result === null) {
            $user->increment('balance', $sellPrice);
            $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon']);
            return $this->error('Gagal memproses transaksi, saldo dikembalikan', 502);
        }

        $providerStatus = $result['status'] ?? 'processing';
        $orderStatus    = match ($providerStatus) {
            'success'              => 'completed',
            'failed', 'error_ip'  => 'failed',
            default               => 'processing',
        };

        $order->update(array_filter([
            'provider_response' => $result,
            'notes'             => $result['raw'] ?? null,
            'status'            => $orderStatus,
            'sn'                => $result['sn'] ?? null,
        ], fn($v) => $v !== null));

        if ($orderStatus === 'failed') {
            $user->increment('balance', $sellPrice);
        }

        $fresh = $order->fresh();
        return $this->success([
            'order_id'          => $fresh->id,
            'ref_id'            => $refId,
            'provider'          => $product['provider'],
            'nominal'           => $nominal,
            'fee'               => $baseFee + $adminFee,
            'sell_price'        => $sellPrice,
            'payment_method'    => 'balance',
            'status'            => $fresh->status,
            'sn'                => $fresh->sn,
            'balance_remaining' => $user->fresh()->balance,
        ], 'Transaksi diproses');
    }

    private function processWithQris($user, array $v, array $product, int $nominal, int $baseFee, int $adminFee, float $sellPrice, float $paymentFee, float $totalPay, string $refId)
    {
        $order = Order::create([
            'user_id'        => $user->id,
            'provider'       => 'okeconnect',
            'order_ref'      => $refId,
            'product_code'   => $v['product_code'],
            'product_name'   => $product['name'] . ' Rp' . number_format($nominal, 0, ',', '.'),
            'category'       => 'ewallet',
            'target'         => $v['destination'],
            'quantity'       => 1,
            'base_price'     => $nominal,
            'markup'         => $adminFee,
            'sell_price'     => $sellPrice,
            'profit'         => $adminFee,
            'payment_method' => 'qris',
            'payment_fee'    => $paymentFee,
            'total_pay'      => $totalPay,
            'payment_status' => 'belum',
            'status'         => 'pending',
        ]);

        $deposit = $this->payment->createQrisForOrder($order, $totalPay);

        if ($deposit === null) {
            $order->update(['status' => 'failed', 'notes' => 'Gagal generate QRIS']);
            return $this->error('Gagal membuat QRIS. Coba lagi nanti.', 502);
        }

        return $this->success([
            'order_id'          => $order->id,
            'ref_id'            => $refId,
            'provider'          => $product['provider'],
            'nominal'           => $nominal,
            'fee'               => $baseFee + $adminFee,
            'sell_price'        => $sellPrice,
            'payment_method'    => 'qris',
            'payment_fee'       => $paymentFee,
            'total_pay'         => $totalPay,
            'qris_content'      => $deposit->qris_content,
            'qris_image_url'    => $deposit->qris_image_url,
            'payinaja_trx_id'   => $deposit->payinaja_trx_id,
            'qris_expired_at'   => $deposit->qris_expired_at->toIso8601String(),
            'deposit_id'        => $deposit->id,
            'status'            => 'pending',
        ], 'Silakan scan QRIS untuk pembayaran', 201);
    }
}
