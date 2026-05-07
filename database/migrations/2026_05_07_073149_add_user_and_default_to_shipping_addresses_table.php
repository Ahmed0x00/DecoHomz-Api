<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Make order_id nullable for user addresses
            $table->foreignId('order_id')->nullable()->change();
            
            // Add is_default
            $table->boolean('is_default')->default(false)->after('postal_code');
            
            // Add address_line_1 and address_line_2
            $table->string('address_line_1', 255)->nullable()->after('phone');
            $table->string('address_line_2', 255)->nullable()->after('address_line_1');
            
            // Add state and country
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->default('Egypt')->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'address_line_1', 'address_line_2', 'state', 'country']);
        });
    }
};
