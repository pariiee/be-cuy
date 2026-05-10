<?php

namespace App\Http\Controllers\Api;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\ProductMarkup;
use App\Services\MidtransService;
use App\Services\SmmPanelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmmPanelController extends BaseApiController
{
    public function __construct(
        protected SmmPanelService $smm,
        protected MidtransService $midtrans
    ) {}

    /**
     * GET /api/smm/apps
     *
     * List all unique app/category names available in the SMM panel.
     * Public endpoint — no auth required.
     */
    public function apps()
    {
        $result = Cache::remember('smmpanel_services', 600, function () {
            return $this->smm->services();
        });

        if ($result === null) {
            Cache::forget('smmpanel_services');
            return $this->error('Gagal mengambil daftar layanan SMM', 502);
        }

        $servicesList = $result['services'] ?? ($result['data'] ?? (is_array($result) ? $result : []));

        $apps = collect($servicesList)
            ->filter(fn($s) => is_array($s) && !empty($s['category']))
            ->pluck('category')
            ->unique()
            ->sort()
            ->values();

        return $this->success([
            'total' => $apps->count(),
            'apps'  => $apps,
        ]);
    }

    /**
     * GET /api/smm/search?app=instagram&min_qty=100
     *
     * Search SMM services by app/category name, sorted cheapest first.
     * Public endpoint — no auth required.
     * Query: ?app=instagram (required), &min_qty=500 (optional filter)
     */
    public function searchByApp(Request $request)
    {
        $app = $request->query('app');
        if (!$app) {
            return $this->error('Parameter ?app wajib diisi. Contoh: ?app=instagram', 422);
        }

        $result = Cache::remember('smmpanel_services', 600, function () {
            return $this->smm->services();
        });

        if ($result === null) {
            Cache::forget('smmpanel_services');
            return $this->error('Gagal mengambil daftar layanan SMM', 502);
        }

        $servicesList = $result['services'] ?? ($result['data'] ?? (is_array($result) ? $result : []));

        $filtered = collect($servicesList)
            ->filter(function ($s) use ($app) {
                if (!is_array($s)) return false;
                $cat = strtolower($s['category'] ?? '');
                $name = strtolower($s['name'] ?? '');
                $keyword = strtolower($app);
                return str_contains($cat, $keyword) || str_contains($name, $keyword);
            })
            ->map(function ($service) {
                $basePrice  = (float) ($service['price'] ?? 0);
                $serviceId  = (string) ($service['id'] ?? '');
                $category   = $service['category'] ?? null;
                $markup     = ProductMarkup::findMarkup('smmpanel', $serviceId, $category);
                $markupAmt  = $markup ? $markup->calculateMarkup($basePrice) : 0;

                return [
                    'id'          => $service['id'] ?? null,
                    'name'        => $service['name'] ?? null,
                    'category'    => $category,
                    'min_order'   => $service['min'] ?? null,
                    'max_order'   => $service['max'] ?? null,
                    'price'       => round($basePrice + $markupAmt, 2),
                    'base_price'  => $basePrice,
                ];
            })
            ->sortBy('price')
            ->values();

        if ($request->filled('min_qty')) {
            $minQty = (int) $request->query('min_qty');
            $filtered = $filtered->filter(fn($s) => ($s['min_order'] ?? 0) <= $minQty)->values();
        }

        return $this->success([
            'app'      => $app,
            'total'    => $filtered->count(),
            'services' => $filtered,
        ]);
    }

    /**
     * GET /api/smm/balance
     *
     * Check SMM Panel account balance.
     */
    public function balance()
    {
        $result = $this->smm->balance();

        if ($result === null) {
            return $this->error('Failed to fetch balance from SMM Panel', 502);
        }

        return $this->success($result);
    }

    /**
     * GET /api/smm/services
     *
     * Get list of available SMM services.
     */
    public function services()
    {
        $result = Cache::remember('smmpanel_services', 600, function () {
            return $this->smm->services();
        });

        if ($result === null) {
            Cache::forget('smmpanel_services');
            return $this->error('Failed to fetch services from SMM Panel', 502);
        }

        // Fayupedia returns: {"status":true,"msg":"OK","services":[...]}
        $servicesKey = isset($result['services']) ? 'services' : (isset($result['data']) ? 'data' : null);
        $servicesList = $servicesKey ? ($result[$servicesKey] ?? []) : (is_array($result) ? $result : []);

        if (is_array($servicesList) && !empty($servicesList)) {
            $servicesList = collect($servicesList)->map(function ($service) {
                if (!is_array($service)) return $service;

                $basePrice = (float) ($service['price'] ?? 0);
                $serviceId = (string) ($service['id'] ?? '');
                $category = $service['category'] ?? null;

                $markup = ProductMarkup::findMarkup('smmpanel', $serviceId, $category);
                $markupAmount = $markup ? $markup->calculateMarkup($basePrice) : 0;

                $service['base_price'] = $basePrice;
                $service['markup'] = round($markupAmount, 2);
                $service['price'] = round($basePrice + $markupAmount, 2);

                return $service;
            })->values()->toArray();

            if ($servicesKey) {
                $result[$servicesKey] = $servicesList;
            } else {
                $result = $servicesList;
            }
        }

        return $this->success($result);
    }

    /**
     * POST /api/smm/order
     *
     * Create a new SMM order.
     * Body: { "service": 1038, "target": "username", "quantity": 100, "service_name": "IG Views", "category": "Instagram", "base_price": 100, "payment_method": "balance" }
     */
    public function order(Request $request)
    {
        $validated = $request->validate([
            'service' => 'required|integer',
            'target' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'service_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:balance,midtrans',
        ]);

        $user = $request->user();
        $basePrice = (float) $validated['base_price'];
        $serviceId = (string) $validated['service'];
        $paymentMethod = $validated['payment_method'];

        // Calculate markup
        $markupModel = ProductMarkup::findMarkup('smmpanel', $serviceId, $validated['category'] ?? null);
        $markupAmount = $markupModel ? $markupModel->calculateMarkup($basePrice) : 0;
        $sellPrice = $basePrice + $markupAmount;
        $refId = 'SMM-' . time() . '-' . Str::random(6);

        if ($paymentMethod === 'balance') {
            return $this->processSmmWithBalance($user, $validated, $basePrice, $markupAmount, $sellPrice, $refId, $serviceId);
        }

        return $this->processSmmWithMidtrans($user, $validated, $basePrice, $markupAmount, $sellPrice, $refId, $serviceId);
    }

    private function processSmmWithBalance($user, array $validated, float $basePrice, float $markupAmount, float $sellPrice, string $refId, string $serviceId)
    {
        if ($user->balance < $sellPrice) {
            return $this->error('Saldo tidak cukup. Dibutuhkan Rp' . number_format($sellPrice, 0, ',', '.') . ', saldo Anda Rp' . number_format($user->balance, 0, ',', '.'), 422);
        }

        $order = DB::transaction(function () use ($user, $validated, $basePrice, $markupAmount, $sellPrice, $refId, $serviceId) {
            $user->decrement('balance', $sellPrice);

            return Order::create([
                'user_id' => $user->id,
                'provider' => 'smmpanel',
                'order_ref' => $refId,
                'product_code' => $serviceId,
                'product_name' => $validated['service_name'] ?? null,
                'category' => $validated['category'] ?? null,
                'target' => $validated['target'],
                'quantity' => $validated['quantity'],
                'base_price' => $basePrice,
                'markup' => $markupAmount,
                'sell_price' => $sellPrice,
                'profit' => $markupAmount,
                'payment_method' => 'balance',
                'payment_fee' => 0,
                'total_pay' => $sellPrice,
                'payment_status' => 'lunas',
                'status' => 'processing',
            ]);
        });

        $result = $this->smm->order(
            $validated['service'],
            $validated['target'],
            $validated['quantity']
        );

        if ($result === null) {
            $user->increment('balance', $sellPrice);
            $order->update(['status' => 'failed', 'notes' => 'Provider tidak merespon']);
            return $this->error('Gagal memproses order SMM, saldo dikembalikan', 502);
        }

        $providerOrderId = $result['order'] ?? $result['data']['order'] ?? null;
        $order->update([
            'provider_response' => $result,
            'order_ref' => $providerOrderId ? (string) $providerOrderId : $refId,
            'status' => isset($result['error']) ? 'failed' : 'processing',
        ]);

        if (isset($result['error'])) {
            $user->increment('balance', $sellPrice);
            $order->update(['status' => 'failed']);
        }

        return $this->success([
            'order_id' => $order->id,
            'ref_id' => $order->fresh()->order_ref,
            'payment_method' => 'balance',
            'sell_price' => $sellPrice,
            'status' => $order->fresh()->status,
            'balance_remaining' => $user->fresh()->balance,
        ], 'Order SMM diproses');
    }

    private function processSmmWithMidtrans($user, array $validated, float $basePrice, float $markupAmount, float $sellPrice, string $refId, string $serviceId)
    {
        if (!$this->midtrans->isEnabled()) {
            return $this->error('Pembayaran Midtrans belum dikonfigurasi.', 503);
        }

        $invoiceNo = 'MID-SMM-' . time() . '-' . Str::random(4);

        $order = Order::create([
            'user_id'        => $user->id,
            'provider'       => 'smmpanel',
            'order_ref'      => $refId,
            'product_code'   => $serviceId,
            'product_name'   => $validated['service_name'] ?? null,
            'category'       => $validated['category'] ?? null,
            'target'         => $validated['target'],
            'quantity'       => $validated['quantity'],
            'base_price'     => $basePrice,
            'markup'         => $markupAmount,
            'sell_price'     => $sellPrice,
            'profit'         => $markupAmount,
            'payment_method' => 'midtrans',
            'payment_fee'    => 0,
            'total_pay'      => $sellPrice,
            'payment_status' => 'belum',
            'status'         => 'pending',
        ]);

        $snap = $this->midtrans->createSnapToken([
            'order_id'     => $invoiceNo,
            'gross_amount' => (int) $sellPrice,
            'name'         => $validated['service_name'] ?? ('SMM Order #' . $serviceId),
            'customer'     => ['first_name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? ''],
        ]);

        if (!$snap) {
            $order->update(['status' => 'failed', 'notes' => 'Gagal generate Snap token']);
            return $this->error('Gagal membuat pembayaran Midtrans. Coba lagi nanti.', 502);
        }

        Deposit::create([
            'user_id'               => $user->id,
            'order_id'              => $order->id,
            'invoice_number'        => $invoiceNo,
            'amount'                => $sellPrice,
            'method'                => 'midtrans',
            'purpose'               => 'order_payment',
            'status'                => 'pending',
            'midtrans_snap_token'   => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
        ]);

        return $this->success([
            'order_id'       => $order->id,
            'ref_id'         => $refId,
            'invoice_no'     => $invoiceNo,
            'payment_method' => 'midtrans',
            'sell_price'     => $sellPrice,
            'snap_token'     => $snap['snap_token'],
            'redirect_url'   => $snap['redirect_url'],
            'client_key'     => config('services.midtrans.client_key'),
            'status'         => 'pending',
        ], 'Silakan selesaikan pembayaran via Midtrans', 201);
    }

    /**
     * GET /api/smm/status/{orderId}
     *
     * Check order status.
     */
    public function status(int $orderId)
    {
        $result = $this->smm->status($orderId);

        if ($result === null) {
            return $this->error('Failed to fetch order status', 502);
        }

        return $this->success($result);
    }

    /**
     * POST /api/smm/refill/{orderId}
     *
     * Request a refill for an order.
     */
    public function refill(int $orderId)
    {
        $result = $this->smm->refill($orderId);

        if ($result === null) {
            return $this->error('Failed to request refill', 502);
        }

        return $this->success($result, 'Refill requested');
    }

    /**
     * GET /api/smm/refill/status/{refillId}
     *
     * Check refill status.
     */
    public function refillStatus(int $refillId)
    {
        $result = $this->smm->refillStatus($refillId);

        if ($result === null) {
            return $this->error('Failed to fetch refill status', 502);
        }

        return $this->success($result);
    }
}
