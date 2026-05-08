<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DepositRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = DepositRule::query()->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search) {
            $query->where('percentage', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $rules = $query->get();
        return response()->json($rules);
    }

    public function show(string $id)
    {
        $rule = DepositRule::findOrFail($id);
        return response()->json($rule);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'minimum_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // If setting this rule as active, deactivate all others
        if (!empty($validated['is_active'])) {
            DepositRule::query()->update(['is_active' => false]);
        }

        $rule = DepositRule::create($validated);

        ActivityLog::settings($request, 'Create Deposit Rule', "Created deposit rule: {$rule->percentage}% (min EGP {$rule->minimum_amount})");

        return response()->json([
            'message' => 'Deposit rule created',
            'rule' => $rule,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'minimum_amount' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rule = DepositRule::findOrFail($id);

        // If setting this rule as active, deactivate all others
        if (!empty($validated['is_active'])) {
            DepositRule::where('id', '!=', $rule->id)->update(['is_active' => false]);
        }

        $rule->update($validated);

        ActivityLog::settings($request, 'Update Deposit Rule', "Updated deposit rule #{$id}: {$rule->percentage}% (min EGP {$rule->minimum_amount})");

        return response()->json([
            'message' => 'Deposit rule updated',
            'rule' => $rule->fresh(),
        ]);
    }

    public function toggle(Request $request, string $id)
    {
        $rule = DepositRule::findOrFail($id);
        $newState = !$rule->is_active;

        // If activating, deactivate all others first
        if ($newState) {
            DepositRule::where('id', '!=', $rule->id)->update(['is_active' => false]);
        }

        $rule->update(['is_active' => $newState]);

        ActivityLog::settings(
            $request,
            'Toggle Deposit Rule',
            ($newState ? 'Activated' : 'Deactivated') . " deposit rule #{$id} ({$rule->percentage}%)"
        );

        return response()->json([
            'message' => 'Deposit rule ' . ($newState ? 'activated' : 'deactivated'),
            'rule' => $rule->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $rule = DepositRule::findOrFail($id);
        $rule->delete();

        ActivityLog::settings($request, 'Delete Deposit Rule', "Deleted deposit rule #{$id}");

        return response()->json(['message' => 'Deposit rule deleted']);
    }
}
