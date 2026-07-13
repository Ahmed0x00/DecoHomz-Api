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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('phone', 20);
            $table->string('email');
            $table->text('address');
            $table->text('workshop_address')->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('e_wallet_number', 30)->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('suspension_ends_at')->nullable();
            $table->timestamp('contract_accepted_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
