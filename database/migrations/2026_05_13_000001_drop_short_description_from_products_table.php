<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'short_description')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'short_description')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->longText('short_description')->nullable()->after('slug');
        });
    }
};
