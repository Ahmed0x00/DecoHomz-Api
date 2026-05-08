<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Drop the old severity column
            $table->dropColumn('severity');

            // Add section (e.g. Orders, Users, Products, Auth, Cart, etc.)
            $table->string('section', 50)->default('General')->after('description');

            // Add result to capture outcome
            $table->string('result', 20)->default('success')->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['section', 'result']);
            $table->string('severity', 20)->default('INFO')->after('description');
        });
    }
};
