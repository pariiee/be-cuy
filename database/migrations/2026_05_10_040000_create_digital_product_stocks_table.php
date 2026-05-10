<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('digital_products')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_sold')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('sold_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_ref')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_sold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_product_stocks');
    }
};
