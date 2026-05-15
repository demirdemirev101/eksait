<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('stock')->default(true);  // true = следи наличност
            $table->decimal('weight', 8, 2)->nullable(); // за Еконт
            $table->decimal('width', 8, 2)->nullable();   // ширина (см)
            $table->decimal('height', 8, 2)->nullable();  // височина (см)
            $table->decimal('length', 8, 2)->nullable();  // дължина (см)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
