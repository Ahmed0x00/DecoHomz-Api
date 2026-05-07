<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('dimensions', 50)->nullable()->after('description');
            $table->string('weight', 30)->nullable()->after('dimensions');
            $table->string('upholstery', 100)->nullable()->after('material');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['dimensions', 'weight', 'upholstery']);
        });
    }
};
