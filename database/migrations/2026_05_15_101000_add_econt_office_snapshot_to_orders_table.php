<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('econt_office_name')->nullable()->after('econt_office_code');
            $table->string('econt_office_address')->nullable()->after('econt_office_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['econt_office_name', 'econt_office_address']);
        });
    }
};
