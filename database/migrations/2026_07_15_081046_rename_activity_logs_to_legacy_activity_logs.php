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
        Schema::rename('activity_logs', 'legacy_activity_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('legacy_activity_logs', 'activity_logs');
    }
};
