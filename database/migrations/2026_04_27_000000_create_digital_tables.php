<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Digital Product Categories ───────────────────────────────
        Schema::create('digital_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Digital Products ─────────────────────────────────────────
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('nama_produk');
            $table->string('kode_produk')->unique();
            $table->string('app_category', 100)->nullable();
            $table->unsignedInteger('harga_user');
            $table->unsignedInteger('harga_reseller');
            $table->boolean('garansi')->default(false);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('stok')->default(-1)->comment('-1 = unlimited');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'app_category']);
            $table->index('kode_produk');
        });

        // ── Redeem Codes ─────────────────────────────────────────────
        Schema::create('redeem_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['discount', 'custom_text'])->default('discount');
            $table->unsignedInteger('discount_value')->default(0);
            $table->string('custom_text')->nullable();
            $table->json('applicable_products')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_usage')->default(0)->comment('0 = unlimited');
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ── Redeem Code Usages ───────────────────────────────────────
        Schema::create('redeem_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('redeem_code_id')->constrained('redeem_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('product_code')->nullable();
            $table->unsignedInteger('discount_applied')->default(0);
            $table->string('output_message')->nullable();
            $table->timestamps();

            $table->index(['redeem_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redeem_code_usages');
        Schema::dropIfExists('redeem_codes');
        Schema::dropIfExists('digital_products');
        Schema::dropIfExists('digital_product_categories');
    }
};
