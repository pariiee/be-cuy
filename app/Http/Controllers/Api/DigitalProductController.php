<?php

namespace App\Http\Controllers\Api;

use App\Models\DigitalProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalProductController extends BaseApiController
{
    /**
     * GET /api/digital/products
     *
     * List products with optional filters.
     * Query params: ?app_category=capcut&search=followers&in_stock=1
     */
    public function index(Request $request): JsonResponse
    {
        $query = DigitalProduct::active();

        if ($request->filled('app_category')) {
            $query->where('app_category', 'like', '%' . $request->query('app_category') . '%');
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%")
                    ->orWhere('app_category', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('in_stock', false)) {
            $query->inStock();
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('nama_produk')
            ->paginate($request->query('per_page', 20));

        return $this->success([
            'products' => collect($products->items())->map(fn($p) => $p->toApiArray()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/productv1
     *
     * List all products in productv1 flat format.
     * Query params: ?app=capcut&search=xxx&in_stock=1&per_page=50
     */
    public function listV1(Request $request): JsonResponse
    {
        $query = DigitalProduct::active();

        if ($request->filled('app')) {
            $query->where('app_category', 'like', '%' . $request->query('app') . '%');
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%")
                    ->orWhere('app_category', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('in_stock', false)) {
            $query->inStock();
        }

        $products = $query->orderBy('app_category')
            ->orderBy('sort_order')
            ->orderBy('nama_produk')
            ->paginate($request->query('per_page', 50));

        return $this->success([
            'products'   => collect($products->items())->map(fn($p) => $p->toApiArray()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/digital/products/{kode_produk}
     *
     * Get a single product by its product code.
     */
    public function show(string $kodeProduk): JsonResponse
    {
        $product = DigitalProduct::where('kode_produk', $kodeProduk)->first();

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        return $this->success([
            'product_data' => $product->toApiArray(),
        ]);
    }

    /**
     * GET /api/productv1/{kodeproduk}
     *
     * Get a single product with PRODUCTV1 format.
     * Output format sesuai spesifikasi:
     * {
     *   "product_data": {
     *     "id": 1,
     *     "nama_produk": "CAPCUT 35H",
     *     "kode_produk": "CC35H",
     *     "kategori": "PRODUCTV1",
     *     "app_category": "Video Editing",
     *     "harga_user": 50000,
     *     "harga_reseller": 45000,
     *     "stok": 10,
     *     "garansi": true
     *   }
     * }
     */
    public function showV1(string $kodeproduk): JsonResponse
    {
        $product = DigitalProduct::where('kode_produk', $kodeproduk)->first();

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        return response()->json([
            'product_data' => [
                'id'             => $product->id,
                'nama_produk'    => $product->nama_produk,
                'kode_produk'    => $product->kode_produk,
                'kategori'       => 'PRODUCTV1',
                'app_category'   => $product->app_category,
                'harga_user'     => $product->harga_user,
                'harga_reseller' => $product->harga_reseller,
                'stok'           => $product->stok,
                'garansi'        => $product->garansi,
            ],
        ]);
    }
}
