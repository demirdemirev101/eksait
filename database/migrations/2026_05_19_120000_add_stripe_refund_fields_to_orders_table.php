<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_status')->index();
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id')->index();
            $table->string('stripe_charge_id')->nullable()->after('stripe_payment_intent_id')->index();
            $table->string('stripe_refund_id')->nullable()->after('stripe_charge_id')->index();
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('stripe_refund_id');
            $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropIndex(['stripe_charge_id']);
            $table->dropIndex(['stripe_refund_id']);
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_charge_id',
                'stripe_refund_id',
                'refunded_amount',
                'refunded_at',
            ]);
        });
    }
};
