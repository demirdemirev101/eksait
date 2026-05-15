<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('cart_id', 'cart_items_cart_id_index');
            $table->index('product_id', 'cart_items_product_id_index');
            $table->dropUnique(['cart_id', 'product_id']);

            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id', 'product_variant_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
            $table->unique(['cart_id', 'product_id']);
        });
    }
};
