<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function register(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json(['message' => 'Only normal users can apply to be vendors.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'workshop_address' => 'nullable|string',
            'bank_account_number' => 'nullable|string|max:50',
            'e_wallet_number' => 'nullable|string|max:30',
            'commercial_register' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'company_name' => $validator->validated()['company_name'],
            'contact_name' => $validator->validated()['contact_name'],
            'phone' => $validator->validated()['phone'],
            'email' => $validator->validated()['email'] ?? null,
            'address' => $validator->validated()['address'],
            'workshop_address' => $validator->validated()['workshop_address'] ?? null,
            'bank_account_number' => $validator->validated()['bank_account_number'] ?? null,
            'e_wallet_number' => $validator->validated()['e_wallet_number'] ?? null,
        ]);

        if ($request->hasFile('commercial_register')) {
            $path = $request->file('commercial_register')->store('vendor_documents');
            $vendor->documents()->create([
                'type' => 'commercial_register',
                'file_path' => $path,
                'status' => 'pending',
            ]);
        }

        if ($request->hasFile('tax_card')) {
            $path = $request->file('tax_card')->store('vendor_documents');
            $vendor->documents()->create([
                'type' => 'tax_card',
                'file_path' => $path,
                'status' => 'pending',
            ]);
        }

        $user->role = 'vendor';
        $user->save();

        return response()->json([
            'message' => 'Vendor application submitted successfully.',
            'vendor' => $vendor
        ], 201);
    }

    public function profile(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 404);
        }
        return response()->json($vendor);
    }

    public function updateProfile(Request $request)
    {
        $vendor = $request->user()->vendor;

        $validated = $request->validate([
            'company_name' => 'string|max:255',
            'contact_name' => 'string|max:255',
            'phone' => 'string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'string',
            'workshop_address' => 'nullable|string',
            'bank_account_number' => 'nullable|string|max:50',
            'e_wallet_number' => 'nullable|string|max:30',
        ]);

        $vendor->update($validated);

        return response()->json(['message' => 'Profile updated successfully.', 'vendor' => $vendor]);
    }

    public function getDocuments(Request $request)
    {
        return response()->json($request->user()->vendor->documents);
    }

    public function uploadDocument(Request $request)
    {
        $vendor = $request->user()->vendor;

        $validated = $request->validate([
            'type' => 'required|string|in:commercial_register,tax_card,id_card,bank_letter,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'label' => 'nullable|string|max:100',
            'document_number' => 'nullable|string|max:100',
        ]);

        $path = $request->file('file')->store('vendor_documents');

        $document = $vendor->documents()->create([
            'type' => $validated['type'],
            'label' => $validated['label'] ?? null,
            'file_path' => $path,
            'document_number' => $validated['document_number'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Document uploaded successfully.', 'document' => $document], 201);
    }

    public function deleteDocument(Request $request, $id)
    {
        $document = $request->user()->vendor->documents()->findOrFail($id);

        if ($document->status === 'verified') {
            return response()->json(['message' => 'Cannot delete a verified document.'], 403);
        }

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        } else if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return response()->json(['message' => 'Document deleted.']);
    }

    public function getFinances(Request $request)
    {
        $vendor = $request->user()->vendor;
        $financeService = app(\App\Services\VendorFinanceService::class);
        
        $balances = $financeService->getBalances($vendor);
        $transactions = $vendor->transactions()->latest()->paginate(20);

        return response()->json([
            'balances' => $balances,
            'transactions' => $transactions
        ]);
    }

    public function getViolations(Request $request)
    {
        $vendor = $request->user()->vendor;
        return response()->json($vendor->violations()->latest()->paginate(20));
    }

    public function viewDocument(Request $request, $id)
    {
        $document = \App\Models\VendorDocument::findOrFail($id);
        
        $user = null;
        if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
            $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        } else {
            $token = $request->query('token') ?? $request->cookie('dh_token') ?? $request->bearerToken();
            if ($token) {
                $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                }
            }
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->isAdmin() && !$user->isSupport()) {
            if ($user->role !== 'vendor' || !$user->vendor || $user->vendor->id !== $document->vendor_id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        if (Storage::disk('local')->exists($document->file_path)) {
            return response()->file(Storage::disk('local')->path($document->file_path));
        }
        
        if (Storage::disk('public')->exists($document->file_path)) {
            return response()->file(Storage::disk('public')->path($document->file_path));
        }

        return response()->json(['message' => 'File not found.'], 404);
    }
}
