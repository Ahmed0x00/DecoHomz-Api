<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('paymob_order_id')->nullable()->after('affiliate_discount')->index();
            $table->string('paymob_transaction_id')->nullable()->after('paymob_order_id')->index();
            $table->json('payment_details')->nullable()->after('paymob_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['paymob_order_id']);
            $table->dropIndex(['paymob_transaction_id']);
            $table->dropColumn(['paymob_order_id', 'paymob_transaction_id', 'payment_details']);
        });
    }
};
