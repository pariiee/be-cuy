<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Product Markups ──────────────────────────────────────────
        Schema::create('product_markups', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('okeconnect');
            $table->string('product_code')->nullable();
            $table->string('category')->nullable();
            $table->enum('markup_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('markup_value', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider', 'product_code']);
            $table->index(['provider', 'category']);
        });

        // ── Orders ───────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('order_ref')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('category')->nullable();
            $table->string('target');
            $table->integer('quantity')->default(1);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('markup', 12, 2)->default(0);
            $table->decimal('sell_price', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            $table->string('payment_method')->default('balance');
            $table->decimal('payment_fee', 12, 2)->default(0);
            $table->decimal('total_pay', 12, 2)->default(0);
            $table->enum('payment_status', ['lunas', 'belum'])->default('belum');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('provider_response')->nullable();
            $table->text('notes')->nullable();
            $table->string('sn')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('payment_status');
            $table->index(['user_id', 'created_at']);
        });

        // ── Deposits ─────────────────────────────────────────────────
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('midtrans');
            $table->string('purpose')->default('deposit');
            $table->enum('status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->string('payment_customer_name')->nullable();
            $table->string('payment_method_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_redirect_url')->nullable();
            $table->string('midtrans_transaction_id')->nullable()->index();
            $table->string('midtrans_payment_type')->nullable();
            $table->string('midtrans_va_number')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'created_at']);
        });

        // ── Settings ─────────────────────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // ── QRIS Fees (legacy — kept for admin UI compatibility) ─────
        Schema::create('qris_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('purpose', ['deposit', 'transaction'])->unique();
            $table->enum('fee_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('fee_value', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── QRIS Markup Settings (legacy — kept for admin UI compatibility) ─
        Schema::create('qris_markup_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('markup_deposit_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('markup_deposit_value', 12, 2)->default(0);
            $table->enum('markup_transaction_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('markup_transaction_value', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('qris_markup_settings')->insert([
            'markup_deposit_type'      => 'fixed',
            'markup_deposit_value'     => 0,
            'markup_transaction_type'  => 'fixed',
            'markup_transaction_value' => 0,
            'is_active'                => true,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        // ── OTP Codes ────────────────────────────────────────────────
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->string('purpose')->default('email_verification');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('qris_markup_settings');
        Schema::dropIfExists('qris_fees');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_markups');
    }
};
