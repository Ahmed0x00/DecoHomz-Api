<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_token');
            $table->string('masked_pan', 20); // e.g. "•••• 1111"
            $table->string('brand', 30)->nullable(); // Visa, Mastercard, Meeza
            $table->string('expiry_month', 2)->nullable();
            $table->string('expiry_year', 4)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'card_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_cards');
    }
};
