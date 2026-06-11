<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table) {
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
        Schema::table('home_banners', function (Blueprint $table) {
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
    }
};
