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
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('event');
            $table->index('created_at');
            $table->index('http_status_code');
            $table->index('http_method');
            $table->index(['log_name', 'created_at']);
            $table->index(['causer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['event']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['http_status_code']);
            $table->dropIndex(['http_method']);
            $table->dropIndex(['log_name', 'created_at']);
            $table->dropIndex(['causer_id', 'created_at']);
        });
    }
};
