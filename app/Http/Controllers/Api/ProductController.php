<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductMarkup;
use App\Services\OkeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseApiController
{
    public function __construct(
        protected OkeConnectService $okeConnect
    ) {}

    /**
     * GET /api/categories
     *
     * Get available product categories.
     */
    public function categories()
    {
        $categories = [
            ['slug' => 'pulsa',          'name' => 'Pulsa',                  'icon' => 'phone'],
            ['slug' => 'kuota_nasional', 'name' => 'Kuota Nasional',         'icon' => 'wifi'],
            ['slug' => 'kuota_telkomsel','name' => 'Kuota Telkomsel',        'icon' => 'wifi'],
            ['slug' => 'bulk_telkomsel', 'name' => 'Paket Bundling Telkomsel','icon' => 'package'],
            ['slug' => 'bulk_cashback',  'name' => 'Paket Cashback',         'icon' => 'tag'],
            ['slug' => 'kuota_byu',      'name' => 'Kuota by.U',             'icon' => 'wifi'],
            ['slug' => 'kuota_indosat',  'name' => 'Kuota Indosat',          'icon' => 'wifi'],
            ['slug' => 'kuota_tri',      'name' => 'Kuota Tri',              'icon' => 'wifi'],
            ['slug' => 'kuota_xl',       'name' => 'Kuota XL',               'icon' => 'wifi'],
            ['slug' => 'kuota_axis',     'name' => 'Kuota Axis',             'icon' => 'wifi'],
            ['slug' => 'kuota_smartfren','name' => 'Kuota Smartfren',        'icon' => 'wifi'],
            ['slug' => 'token_pln',      'name' => 'Token PLN',              'icon' => 'zap'],
            ['slug' => 'saldo_gojek',    'name' => 'E-Wallet (GoPay, OVO, Dana, ShopeePay, dll)', 'icon' => 'wallet'],
            ['slug' => 'tagihan',        'name' => 'Tagihan',                'icon' => 'file-text'],
            ['slug' => 'air_pdam',       'name' => 'Air PDAM',               'icon' => 'droplet'],
            ['slug' => 'pascabayar',     'name' => 'Pascabayar',             'icon' => 'smartphone'],
        ];

        return $this->success($categories);
    }

    /**
     * GET /api/products?category=pulsa
     *
     * Get list of products from OkeConnect by category.
     * Available categories: pulsa, data, game, pln, etc.
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'pulsa');

        // Cache raw product data for 10 minutes
        $products = Cache::remember("okeconnect_products_{$category}", 600, function () use ($category) {
            return $this->okeConnect->getProducts($category);
        });

        if ($products === null) {
            Cache::forget("okeconnect_products_{$category}");
            return $this->error('Failed to fetch products from provider', 502);
        }

        // Group products by 'produk' field
        $grouped = collect($products)
            ->where('status', '1') // only active products
            ->groupBy('produk')
            ->map(function ($items, $provider) use ($category) {
                return [
                    'provider' => $provider,
                    'items' => $items->map(function ($item) use ($category) {
                        $basePrice = (int) $item['harga'];
                        $markup = ProductMarkup::findMarkup('okeconnect', $item['kode'], $category);
                        $markupAmount = $markup ? $markup->calculateMarkup($basePrice) : 0;

                        return [
                            'code' => $item['kode'],
                            'description' => $item['keterangan'],
                            'category' => $item['kategori'],
                            'base_price' => $basePrice,
                            'markup' => (int) $markupAmount,
                            'price' => (int) ($basePrice + $markupAmount),
                        ];
                    })->values(),
                ];
            })
            ->values();

        return $this->success([
            'category' => $category,
            'providers' => $grouped,
        ]);
    }

    /**
     * GET /api/products/{category}/{provider}
     *
     * Get products filtered by provider name (e.g. Telkomsel, Indosat).
     */
    public function byProvider(Request $request, string $category, string $provider)
    {
        $products = Cache::remember("okeconnect_products_{$category}", 600, function () use ($category) {
            return $this->okeConnect->getProducts($category);
        });

        if ($products === null) {
            Cache::forget("okeconnect_products_{$category}");
            return $this->error('Failed to fetch products from provider', 502);
        }

        $filtered = collect($products)
            ->where('status', '1')
            ->filter(fn($item) => strtolower($item['produk']) === strtolower($provider))
            ->map(function ($item) use ($category) {
                $basePrice = (int) $item['harga'];
                $markup = ProductMarkup::findMarkup('okeconnect', $item['kode'], $category);
                $markupAmount = $markup ? $markup->calculateMarkup($basePrice) : 0;

                return [
                    'code' => $item['kode'],
                    'description' => $item['keterangan'],
                    'provider' => $item['produk'],
                    'category' => $item['kategori'],
                    'base_price' => $basePrice,
                    'markup' => (int) $markupAmount,
                    'price' => (int) ($basePrice + $markupAmount),
                ];
            })
            ->values();

        if ($filtered->isEmpty()) {
            return $this->error('Provider not found', 404);
        }

        return $this->success([
            'category' => $category,
            'provider' => $provider,
            'products' => $filtered,
        ]);
    }
}
