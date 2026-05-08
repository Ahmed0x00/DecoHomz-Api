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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->json('response_data')->nullable()->after('payload');
            $table->json('old_values')->nullable()->after('response_data');
            $table->string('resource_type')->nullable()->after('action');
            $table->string('resource_id')->nullable()->after('resource_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['response_data', 'old_values', 'resource_type', 'resource_id']);
        });
    }
};
