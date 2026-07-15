<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])->latest();

        // Filter by section (log_name in Spatie)
        if ($request->has('section')) {
            $query->where('log_name', $request->section);
        }

        // Filter by resource type (subject_type in Spatie)
        if ($request->has('resource_type')) {
            $query->where('subject_type', 'like', '%' . $request->resource_type . '%');
        }

        // Filter by user (causer_id in Spatie)
        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search across description or user name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('log_name', 'like', "%$search%")
                  ->orWhereHasMorph('causer', [\App\Models\User::class], function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%")
                         ->orWhere('email', 'like', "%$search%");
                  });
            });
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show($id)
    {
        $log = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])->findOrFail($id);
        return response()->json($log);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $log = \Spatie\Activitylog\Models\Activity::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Log deleted successfully']);
    }

    /**
     * Clear all logs.
     */
    public function clear()
    {
        \Spatie\Activitylog\Models\Activity::truncate();
        return response()->json(['message' => 'All logs cleared']);
    }
}
