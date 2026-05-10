<?php

namespace App\Http\Controllers\Api;

use App\Models\DigitalProduct;
use App\Models\DigitalProductCategory;
use App\Models\RedeemCode;
use App\Services\RedeemCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDigitalProductController extends BaseApiController
{
    public function __construct(
        protected RedeemCodeService $redeemService
    ) {}

    // ═══════════════════════════════════════════════════════════
    //  KATEGORI MANAGEMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/admin/digital/categories
     *
     * List all categories (including inactive).
     */
    public function listCategories(): JsonResponse
    {
        $categories = DigitalProductCategory::ordered()
            ->withCount('products')
            ->get();

        return $this->success([
            'categories' => $categories->map(fn($cat) => [
                'id'              => $cat->id,
                'nama_kategori'   => $cat->nama_kategori,
                'slug'            => $cat->slug,
                'icon'            => $cat->icon,
                'is_active'       => $cat->is_active,
                'products_count'  => $cat->products_count,
                'created_at'      => $cat->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/admin/digital/categories
     *
     * Create a new category.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'slug'          => 'required|string|max:100|unique:digital_product_categories,slug',
            'icon'          => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $category = DigitalProductCategory::create($validated);

        return $this->success([
            'category' => $category,
        ], 'Kategori berhasil ditambahkan!', 201);
    }

    /**
     * PUT /api/admin/digital/categories/{id}
     *
     * Update an existing category.
     */
    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $category = DigitalProductCategory::find($id);

        if (!$category) {
            return $this->error('Kategori tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'nama_kategori' => 'sometimes|string|max:100',
            'slug'          => 'sometimes|string|max:100|unique:digital_product_categories,slug,' . $id,
            'icon'          => 'nullable|string|max:50',
            'is_active'     => 'boolean',
        ]);

        $category->update($validated);

        return $this->success([
            'category' => $category->fresh(),
        ], 'Kategori berhasil diperbarui!');
    }

    /**
     * DELETE /api/admin/digital/categories/{id}
     *
     * Delete a category (cascades to products).
     */
    public function destroyCategory(int $id): JsonResponse
    {
        $category = DigitalProductCategory::find($id);

        if (!$category) {
            return $this->error('Kategori tidak ditemukan.', 404);
        }

        $productCount = $category->products()->count();
        $category->delete();

        return $this->success(null, "Kategori '{$category->nama_kategori}' beserta {$productCount} produk berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════
    //  PRODUK MANAGEMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/admin/digital/products
     *
     * List all products (including inactive) with pagination.
     */
    public function listProducts(Request $request): JsonResponse
    {
        $query = DigitalProduct::query();

        if ($request->filled('app_category')) {
            $query->where('app_category', $request->query('app_category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->query('stock_status') === 'in_stock') {
                $query->where('stok', '>', 0);
            } elseif ($request->query('stock_status') === 'out_of_stock') {
                $query->where('stok', '<=', 0);
            }
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return $this->success([
            'products' => collect($products->items())->map(fn($p) => [
                'id'             => $p->id,
                'nama_produk'    => $p->nama_produk,
                'kode_produk'    => $p->kode_produk,
                'app_category'   => $p->app_category,
                'harga_user'     => $p->harga_user,
                'harga_reseller' => $p->harga_reseller,
                'garansi'        => $p->garansi,
                'stok'           => $p->stok,
                'is_active'      => $p->is_active,
                'deskripsi'      => $p->deskripsi,
                'created_at'     => $p->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/digital/products
     *
     * Create a new product manually (internal product).
     *
     * Request body:
     * {
     *   "category_id": 1,
     *   "nama_produk": "CAPCUT 35H",
     *   "kode_produk": "CC35H",
     *   "app_category": "Video Editing",
     *   "harga_user": 50000,
     *   "harga_reseller": 45000,
     *   "garansi": true,
     *   "stok": 10
     * }
     */
    public function storeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'    => 'nullable|integer',
            'nama_produk'    => 'required|string|max:200',
            'kode_produk'    => 'required|string|max:50|unique:digital_products,kode_produk',
            'harga_user'     => 'required|integer|min:0',
            'harga_reseller' => 'required|integer|min:0',
            'garansi'        => 'integer|min:0',
            'deskripsi'      => 'nullable|string|max:1000',
            'sort_order'     => 'integer|min:0',
            'stock_items'    => 'nullable|array',
            'stock_items.*'  => 'string',
        ]);

        $product = DigitalProduct::create(array_merge($validated, ['stok' => 0, 'is_active' => false]));

        if (!empty($validated['stock_items'])) {
            $product->addStockItems($validated['stock_items']);
        }

        return $this->success([
            'product_data' => $product->fresh()->toApiArray(),
        ], 'Produk berhasil ditambahkan!', 201);
    }

    /**
     * PUT /api/admin/digital/products/{id}
     *
     * Update an existing product.
     */
    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $product = DigitalProduct::find($id);

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'category_id'    => 'sometimes|nullable|integer',
            'nama_produk'    => 'sometimes|string|max:200',
            'kode_produk'    => 'sometimes|string|max:50|unique:digital_products,kode_produk,' . $id,
            'harga_user'     => 'sometimes|integer|min:0',
            'harga_reseller' => 'sometimes|integer|min:0',
            'garansi'        => 'sometimes|integer|min:0',
            'deskripsi'      => 'nullable|string|max:1000',
            'sort_order'     => 'sometimes|integer|min:0',
            'stock_items'    => 'nullable|array',
            'stock_items.*'  => 'string',
        ]);

        $product->update(collect($validated)->except('stock_items')->toArray());

        if (!empty($validated['stock_items'])) {
            $product->addStockItems($validated['stock_items']);
        }

        return $this->success([
            'product_data' => $product->fresh()->toApiArray(),
        ], 'Produk berhasil diperbarui!');
    }

    /**
     * POST /api/admin/digital/products/{id}/restock
     *
     * Restock a product (add to existing stock).
     *
     * Request body:
     * { "jumlah": 10 }
     *
     * Response:
     * {
     *   "product_data": { ... },
     *   "restock_info": {
     *     "stok_sebelumnya": 5,
     *     "ditambahkan": 10,
     *     "stok_sekarang": 15
     *   }
     * }
     */
    public function restockProduct(Request $request, int $id): JsonResponse
    {
        $product = DigitalProduct::find($id);

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'string',
        ]);

        $stokSebelumnya = $product->stok;
        $added = $product->addStockItems($validated['items']);
        $fresh = $product->fresh();

        return $this->success([
            'product_data' => $fresh->toApiArray(),
            'restock_info' => [
                'stok_sebelumnya' => $stokSebelumnya,
                'ditambahkan'     => $added,
                'stok_sekarang'   => $fresh->stok,
            ],
        ], "Restock berhasil! Stok {$fresh->nama_produk}: {$stokSebelumnya} → {$fresh->stok}");
    }

    /**
     * DELETE /api/admin/digital/products/{id}
     *
     * Delete a product.
     */
    public function destroyProduct(int $id): JsonResponse
    {
        $product = DigitalProduct::find($id);

        if (!$product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $name = $product->nama_produk;
        $product->delete();

        return $this->success(null, "Produk '{$name}' berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════
    //  REDEEM CODE MANAGEMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/admin/digital/redeem-codes
     *
     * List all redeem codes.
     */
    public function listRedeemCodes(): JsonResponse
    {
        $codes = RedeemCode::withCount('usages')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'redeem_codes' => $codes->map(fn($rc) => [
                'id'                  => $rc->id,
                'code'                => $rc->code,
                'type'                => $rc->type,
                'discount_value'      => $rc->discount_value,
                'custom_text'         => $rc->custom_text,
                'applicable_products' => $rc->applicable_products,
                'is_active'           => $rc->is_active,
                'max_usage'           => $rc->max_usage,
                'used_count'          => $rc->used_count,
                'usages_count'        => $rc->usages_count,
                'valid_from'          => $rc->valid_from?->toIso8601String(),
                'valid_until'         => $rc->valid_until?->toIso8601String(),
                'is_valid'            => $rc->isValid(),
                'created_at'          => $rc->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/admin/digital/redeem-codes
     *
     * Create a new redeem code.
     *
     * Supports multi-product: applicable_products can contain up to 5 product codes.
     * Types: 'discount' (potongan harga) or 'custom_text' (pesan khusus/bonus teks).
     *
     * Request body:
     * {
     *   "code": "HEMAT5K",
     *   "type": "discount",
     *   "discount_value": 5000,
     *   "applicable_products": ["CC35H", "IG1K", "SP100"],
     *   "max_usage": 100,
     *   "is_active": true
     * }
     */
    public function storeRedeemCode(Request $request): JsonResponse
    {
        $request->validate([
            'code'                 => 'required|string|max:50|unique:redeem_codes,code',
            'type'                 => 'required|in:discount,custom_text',
            'discount_value'       => 'required_if:type,discount|integer|min:0',
            'custom_text'          => 'required_if:type,custom_text|nullable|string|max:500',
            'applicable_products'  => 'nullable|array|max:5',
            'applicable_products.*' => 'string|max:50',
            'is_active'            => 'boolean',
            'max_usage'            => 'integer|min:0',
            'valid_from'           => 'nullable|date',
            'valid_until'          => 'nullable|date|after_or_equal:valid_from',
        ]);

        $result = $this->redeemService->create($request->all());

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success([
            'redeem_logic' => $result['data'],
        ], $result['message'], 201);
    }

    /**
     * PUT /api/admin/digital/redeem-codes/{id}
     *
     * Update a redeem code.
     */
    public function updateRedeemCode(Request $request, int $id): JsonResponse
    {
        $redeemCode = RedeemCode::find($id);

        if (!$redeemCode) {
            return $this->error('Kode redeem tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'code'                 => 'sometimes|string|max:50|unique:redeem_codes,code,' . $id,
            'type'                 => 'sometimes|in:discount,custom_text',
            'discount_value'       => 'sometimes|integer|min:0',
            'custom_text'          => 'nullable|string|max:500',
            'applicable_products'  => 'nullable|array|max:5',
            'applicable_products.*' => 'string|max:50',
            'is_active'            => 'boolean',
            'max_usage'            => 'integer|min:0',
            'valid_from'           => 'nullable|date',
            'valid_until'          => 'nullable|date|after_or_equal:valid_from',
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        // Validate applicable_products if provided
        if (!empty($validated['applicable_products'])) {
            if (count($validated['applicable_products']) > RedeemCode::MAX_APPLICABLE_PRODUCTS) {
                return $this->error('Maksimal ' . RedeemCode::MAX_APPLICABLE_PRODUCTS . ' produk per kode redeem.', 422);
            }

            $existingCodes = DigitalProduct::whereIn('kode_produk', $validated['applicable_products'])
                ->pluck('kode_produk')
                ->toArray();

            $invalidCodes = array_diff($validated['applicable_products'], $existingCodes);
            if (!empty($invalidCodes)) {
                return $this->error('Kode produk tidak valid: ' . implode(', ', $invalidCodes), 422);
            }
        }

        $redeemCode->update($validated);

        return $this->success([
            'redeem_logic' => $redeemCode->fresh(),
        ], 'Kode redeem berhasil diperbarui!');
    }

    /**
     * DELETE /api/admin/digital/redeem-codes/{id}
     *
     * Delete a redeem code.
     */
    public function destroyRedeemCode(int $id): JsonResponse
    {
        $redeemCode = RedeemCode::find($id);

        if (!$redeemCode) {
            return $this->error('Kode redeem tidak ditemukan.', 404);
        }

        $code = $redeemCode->code;
        $redeemCode->delete();

        return $this->success(null, "Kode redeem '{$code}' berhasil dihapus.");
    }
}
