<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name', 100);          // e.g. "Navy Blue"
            $table->string('hex_code', 7);       // e.g. "#1a365d"
            $table->string('color_slug', 100);    // URL-safe identifier
            $table->decimal('price_modifier', 10, 2)->default(0)->comment('Additional price for this color. 0 = same as product base price.');
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'color_slug']);
            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
