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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('home_banner_eyebrow')->nullable()->after('delivery_enabled');
            $table->string('home_banner_title')->nullable()->after('home_banner_eyebrow');
            $table->text('home_banner_subtitle')->nullable()->after('home_banner_title');
            $table->string('home_banner_button_text')->nullable()->after('home_banner_subtitle');
            $table->string('home_banner_button_url')->nullable()->after('home_banner_button_text');
            $table->string('home_banner_image')->nullable()->after('home_banner_button_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_banner_eyebrow',
                'home_banner_title',
                'home_banner_subtitle',
                'home_banner_button_text',
                'home_banner_button_url',
                'home_banner_image',
            ]);
        });
    }
};

