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
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('reviewer_name')->nullable()->after('user_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('fake_sold_count')->nullable()->after('stock');
            $table->integer('min_viewing_count')->nullable()->after('fake_sold_count');
            $table->integer('max_viewing_count')->nullable()->after('min_viewing_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fake_sold_count', 'min_viewing_count', 'max_viewing_count']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('reviewer_name');
        });
    }
};
