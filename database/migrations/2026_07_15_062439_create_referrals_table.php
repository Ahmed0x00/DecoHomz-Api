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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('order_subtotal', 12, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->string('commission_status', 20)->default('pending'); // pending|holding|approved|paid|revoked|clawback
            $table->timestamp('hold_started_at')->nullable();
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_reference', 100)->nullable();
            $table->string('revoke_reason', 255)->nullable();
            $table->string('buyer_ip_address', 45)->nullable();
            $table->json('fraud_flags')->nullable();
            $table->timestamps();

            $table->index('commission_status');
            $table->index(['affiliate_id', 'commission_status']);
            $table->index(['commission_status', 'hold_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
