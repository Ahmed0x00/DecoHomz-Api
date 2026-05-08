<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('refund_status', 20)->nullable()->after('payment_status');
            $table->string('refund_reason', 500)->nullable()->after('refund_status');
            $table->timestamp('refund_handled_at')->nullable()->after('refund_reason');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_status', 'refund_reason', 'refund_handled_at']);
        });
    }
};
