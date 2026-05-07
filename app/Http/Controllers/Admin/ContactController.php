<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($contacts);
    }

    public function show(string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        // Auto-mark as read on view
        if ($contact->status === 'new') {
            $contact->update(['status' => 'read']);
        }

        return response()->json(['contact' => $contact]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|in:new,read,replied',
        ]);

        $contact->update($validated);

        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => $contact->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }

    public function markReplied(string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->update(['status' => 'replied']);

        return response()->json([
            'message' => 'Contact marked as replied',
            'contact' => $contact->fresh(),
        ]);
    }
}
