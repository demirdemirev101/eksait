<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_de')->nullable()->after('name_en');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_de')->nullable()->after('name_en');
            $table->longText('description_en')->nullable()->after('description');
            $table->longText('description_de')->nullable()->after('description_en');
            $table->longText('extra_information_en')->nullable()->after('extra_information');
            $table->longText('extra_information_de')->nullable()->after('extra_information_en');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->string('size_en')->nullable()->after('size');
            $table->string('size_de')->nullable()->after('size_en');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn('size_en');
            $table->dropColumn('size_de');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'name_de', 'description_en', 'description_de', 'extra_information_en', 'extra_information_de']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('name_en');
            $table->dropColumn('name_de');
        });
    }
};
