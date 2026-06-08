<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_de')->nullable()->after('name_en');
            $table->longText('description_en')->nullable()->after('description');
            $table->longText('description_de')->nullable()->after('description_en');
            $table->longText('extra_information_en')->nullable()->after('extra_information');
            $table->longText('extra_information_de')->nullable()->after('extra_information_en');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_de')->nullable()->after('name_en');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->string('size_en')->nullable()->after('size');
            $table->string('size_de')->nullable()->after('size_en');
        });

        Schema::table('home_banners', function (Blueprint $table): void {
            $table->string('eyebrow_en')->nullable()->after('eyebrow');
            $table->string('eyebrow_de')->nullable()->after('eyebrow_en');
            $table->string('title_en')->nullable()->after('title');
            $table->string('title_de')->nullable()->after('title_en');
            $table->text('subtitle_en')->nullable()->after('subtitle');
            $table->text('subtitle_de')->nullable()->after('subtitle_en');
            $table->string('button_text_en')->nullable()->after('button_text');
            $table->string('button_text_de')->nullable()->after('button_text_en');
        });
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table): void {
            $table->dropColumn([
                'eyebrow_en',
                'eyebrow_de',
                'title_en',
                'title_de',
                'subtitle_en',
                'subtitle_de',
                'button_text_en',
                'button_text_de',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn(['size_en', 'size_de']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['name_en', 'name_de']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'name_en',
                'name_de',
                'description_en',
                'description_de',
                'extra_information_en',
                'extra_information_de',
            ]);
        });
    }
};
