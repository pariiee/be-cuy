<?php

namespace App\Http\Controllers\Api;

use App\Models\DigitalProductCategory;
use Illuminate\Http\JsonResponse;

class DigitalProductCategoryController extends BaseApiController
{
    /**
     * GET /api/digital/categories
     *
     * Get all active categories with product count.
     */
    public function index(): JsonResponse
    {
        $categories = DigitalProductCategory::active()
            ->ordered()
            ->withCount(['products' => fn($q) => $q->active()])
            ->get();

        return $this->success([
            'categories' => $categories->map(fn($cat) => [
                'id'            => $cat->id,
                'nama_kategori' => $cat->nama_kategori,
                'slug'          => $cat->slug,
                'icon'          => $cat->icon,
                'total_produk'  => $cat->products_count,
            ]),
            'total_categories' => $categories->count(),
        ]);
    }

    /**
     * GET /api/digital/categories/{slug}
     *
     * Get a single category with its products.
     * Example: /api/digital/categories/video-editing
     */
    public function show(string $slug): JsonResponse
    {
        $category = DigitalProductCategory::active()
            ->where('slug', $slug)
            ->first();

        if (!$category) {
            return $this->error('Kategori tidak ditemukan.', 404);
        }

        $products = $category->products()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('nama_produk')
            ->get();

        return $this->success([
            'kategori' => [
                'id'            => $category->id,
                'nama_kategori' => $category->nama_kategori,
                'slug'          => $category->slug,
                'icon'          => $category->icon,
            ],
            'products' => $products->map(fn($p) => $p->toApiArray()),
        ]);
    }

    /**
     * GET /api/digital/all-products
     *
     * Get ALL products grouped by category.
     */
    public function allProducts(): JsonResponse
    {
        $categories = DigitalProductCategory::active()
            ->ordered()
            ->with(['products' => fn($q) => $q->active()->orderBy('sort_order')->orderBy('nama_produk')])
            ->get();

        return $this->success([
            'categories' => $categories->map(fn($cat) => [
                'id'            => $cat->id,
                'nama_kategori' => $cat->nama_kategori,
                'slug'          => $cat->slug,
                'icon'          => $cat->icon,
                'products'      => $cat->products->map(fn($p) => $p->toApiArray()),
            ]),
            'total_products' => $categories->sum(fn($c) => $c->products->count()),
        ]);
    }

    /**
     * GET /api/category/{nama_apps}
     *
     * Get all products for a specific app by app_category name (case-insensitive).
     * Example: /api/category/capcut → semua produk dengan app_category like 'capcut'
     */
    public function byApp(string $nama_apps): JsonResponse
    {
        $products = \App\Models\DigitalProduct::active()
            ->where('app_category', 'like', '%' . $nama_apps . '%')
            ->orderBy('sort_order')
            ->orderBy('nama_produk')
            ->get();

        if ($products->isEmpty()) {
            return $this->error("Produk dengan kategori '{$nama_apps}' tidak ditemukan.", 404);
        }

        return $this->success([
            'app_category'   => $nama_apps,
            'total_produk'   => $products->count(),
            'products'       => $products->map(fn($p) => $p->toApiArray()),
        ]);
    }
}
