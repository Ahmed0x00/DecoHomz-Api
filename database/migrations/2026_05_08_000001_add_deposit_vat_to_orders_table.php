<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('total');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('deposit_amount');
            $table->string('payment_status', 20)->default('unpaid')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'vat_amount']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->change();
        });
    }
};
