<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

return new class extends Migration
{
    public function up(): void
    {
        $legacyLogs = DB::table('legacy_activity_logs')->get();

        foreach ($legacyLogs as $log) {
            $properties = [
                'attributes' => json_decode($log->payload, true) ?: [],
                'old' => json_decode($log->old_values, true) ?: [],
                'ip' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'response_data' => json_decode($log->response_data, true) ?: [],
                'legacy_action' => $log->action,
                'legacy_result' => $log->result,
            ];

            // Map resource_type to a full model class if possible
            $subjectType = null;
            if ($log->resource_type) {
                $type = ucfirst(strtolower($log->resource_type));
                if (in_array($type, ['Order', 'Product', 'User', 'Category', 'Coupon', 'Review', 'Cart'])) {
                    $subjectType = "App\\Models\\" . $type;
                } else {
                    $subjectType = $log->resource_type;
                }
            }

            // Figure out the "event" based on action or result
            $event = 'info';
            $actionLower = strtolower($log->action);
            if (str_contains($actionLower, 'creat')) $event = 'created';
            elseif (str_contains($actionLower, 'updat')) $event = 'updated';
            elseif (str_contains($actionLower, 'delet')) $event = 'deleted';
            elseif (str_contains($actionLower, 'login')) $event = 'login';
            elseif (str_contains($actionLower, 'logout')) $event = 'logout';

            DB::table('activity_log')->insert([
                'log_name' => $log->section ?: 'General',
                'description' => $log->description ?: $log->action,
                'subject_type' => $subjectType,
                'subject_id' => $log->resource_id,
                'event' => $event,
                'causer_type' => $log->user_id ? 'App\\Models\\User' : null,
                'causer_id' => $log->user_id,
                'properties' => json_encode($properties),
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        // Not reversing
    }
};
