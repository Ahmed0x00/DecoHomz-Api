<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ActivityLog;
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

        ActivityLog::contacts($request, 'Update Contact', ActivityLog::userName($request) . " updated contact #{$contact->id} from '{$contact->name}' (status: {$contact->status})", $contact);

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

        ActivityLog::contacts($request, 'Delete Contact', ActivityLog::userName($request) . " deleted contact #{$id} from '{$contact->name}'", null, 'deletion');

        return response()->json(['message' => 'Contact deleted successfully']);
    }

    public function markReplied(string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->update(['status' => 'replied']);

        ActivityLog::contacts($request, 'Mark Contact Replied', ActivityLog::userName($request) . " marked contact #{$contact->id} from '{$contact->name}' as replied", $contact);

        return response()->json([
            'message' => 'Contact marked as replied',
            'contact' => $contact->fresh(),
        ]);
    }
}
