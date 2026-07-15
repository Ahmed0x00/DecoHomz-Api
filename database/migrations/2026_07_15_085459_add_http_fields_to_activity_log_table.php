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
            $table->string('http_method', 10)->nullable()->after('event');
            $table->string('url', 500)->nullable()->after('http_method');
            $table->unsignedSmallInteger('http_status_code')->nullable()->after('url');
            $table->unsignedInteger('response_time_ms')->nullable()->after('http_status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn([
                'http_method',
                'url',
                'http_status_code',
                'response_time_ms'
            ]);
        });
    }
};
