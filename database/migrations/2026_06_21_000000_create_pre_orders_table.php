<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->nullable();
            $table->string('phone', 30);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'contacted', 'confirmed', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
