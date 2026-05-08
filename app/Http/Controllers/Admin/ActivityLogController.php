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
        $query = ActivityLog::with('user')->latest();

        // Filter by section
        if ($request->has('section')) {
            $query->where('section', $request->section);
        }

        // Filter by result (success, failure, deletion, warning, read, info)
        if ($request->has('result')) {
            $query->where('result', $request->result);
        }

        // Filter by resource type
        if ($request->has('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search across action, description, user name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%")
                         ->orWhere('email', 'like', "%$search%");
                  });
            });
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        return response()->json($log);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $log = ActivityLog::findOrFail($id);
        $log->delete();
        return response()->json(['message' => 'Log deleted successfully']);
    }

    /**
     * Clear all logs.
     */
    public function clear()
    {
        ActivityLog::truncate();
        return response()->json(['message' => 'All logs cleared']);
    }
}
