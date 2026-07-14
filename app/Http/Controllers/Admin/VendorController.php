<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorDocument;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        $vendor = Vendor::with(['user', 'documents', 'violations.admin'])->findOrFail($id);
        
        $financeService = app(\App\Services\VendorFinanceService::class);
        $balances = $financeService->getBalances($vendor);

        return response()->json([
            'vendor' => $vendor,
            'balances' => $balances,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        $vendor->update([
            'status' => 'active',
            'contract_accepted_at' => now(),
        ]);

        return response()->json(['message' => 'Vendor approved successfully.', 'vendor' => $vendor]);
    }

    public function reject(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['status' => 'rejected']);
        return response()->json(['message' => 'Vendor rejected.']);
    }

    public function suspend(Request $request, $id)
    {
        $request->validate(['days' => 'required|integer|min:1']);
        
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'status' => 'suspended',
            'suspension_ends_at' => now()->addDays($request->days)
        ]);

        return response()->json(['message' => 'Vendor suspended.', 'vendor' => $vendor]);
    }

    public function ban(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'status' => 'banned',
            'suspension_ends_at' => null
        ]);
        return response()->json(['message' => 'Vendor banned.', 'vendor' => $vendor]);
    }

    public function reinstate(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'status' => 'active',
            'suspension_ends_at' => null
        ]);
        return response()->json(['message' => 'Vendor reinstated.', 'vendor' => $vendor]);
    }

    public function updateNotes(Request $request, $id)
    {
        $request->validate(['admin_notes' => 'required|string']);
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['admin_notes' => $request->admin_notes]);
        return response()->json(['message' => 'Notes updated.', 'vendor' => $vendor]);
    }

    public function verifyDocument(Request $request, $id)
    {
        $document = VendorDocument::findOrFail($id);
        $document->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $document->reviewHistories()->create([
            'admin_id' => $request->user()->id,
            'from_status' => 'pending',
            'to_status' => 'verified',
        ]);

        return response()->json(['message' => 'Document verified.', 'document' => $document]);
    }

    public function rejectDocument(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        
        $document = VendorDocument::findOrFail($id);
        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $document->reviewHistories()->create([
            'admin_id' => $request->user()->id,
            'from_status' => 'pending',
            'to_status' => 'rejected',
            'comment' => $request->rejection_reason,
        ]);

        return response()->json(['message' => 'Document rejected.', 'document' => $document]);
    }

    public function issueViolation(Request $request, $vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        
        $validated = $request->validate([
            'violation_type' => 'required|string',
            'description' => 'required|string',
            'severity_points' => 'required|integer|min:1|max:10',
            'action_taken' => 'required|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $validated['admin_id'] = $request->user()->id;

        $service = app(\App\Services\VendorViolationService::class);
        $violation = $service->issueViolation($vendor, $validated);

        return response()->json(['message' => 'Violation issued successfully.', 'violation' => $violation], 201);
    }

    public function sendNotification(Request $request, $vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'action_url' => 'nullable|url'
        ]);

        $vendor->notify(new \App\Notifications\VendorNotification(
            $validated['title'],
            $validated['message'],
            $validated['type'],
            $validated['action_url'] ?? null
        ));

        return response()->json(['message' => 'Notification sent successfully.']);
    }

    public function notifyAll(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'action_url' => 'nullable|url'
        ]);

        $announcement = \App\Models\VendorAnnouncement::create($validated);

        return response()->json(['message' => 'Global notification sent successfully.', 'announcement' => $announcement], 201);
    }
}
