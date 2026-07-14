<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VendorNotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated vendor.
     */
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Not a vendor'], 403);
        }

        // Fetch personal notifications
        $personalNotifications = $vendor->notifications()->get();
        $unreadPersonal = $vendor->unreadNotifications()->count();

        // Fetch global announcements
        $globalAnnouncements = \App\Models\VendorAnnouncement::latest()->get();
        
        // Fetch read receipts for this vendor
        $readAnnouncements = \App\Models\VendorAnnouncementRead::where('vendor_id', $vendor->id)
                                ->pluck('vendor_announcement_id')
                                ->toArray();

        $unreadGlobal = 0;
        $mergedNotifications = [];

        // Format personal notifications
        foreach ($personalNotifications as $notification) {
            $mergedNotifications[] = [
                'id' => $notification->id,
                'data' => $notification->data,
                'created_at' => $notification->created_at,
                'read_at' => $notification->read_at,
                'is_global' => false,
            ];
        }

        // Format global announcements
        foreach ($globalAnnouncements as $announcement) {
            $isRead = in_array($announcement->id, $readAnnouncements);
            if (!$isRead) $unreadGlobal++;

            $mergedNotifications[] = [
                'id' => 'global_' . $announcement->id,
                'data' => [
                    'title' => $announcement->title,
                    'message' => $announcement->message,
                    'type' => $announcement->type,
                    'action_url' => $announcement->action_url,
                ],
                'created_at' => $announcement->created_at,
                'read_at' => $isRead ? now() : null, // Mock read_at for UI
                'is_global' => true,
            ];
        }

        // Sort merged by created_at desc
        usort($mergedNotifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json([
            'unread_count' => $unreadPersonal + $unreadGlobal,
            'notifications' => $mergedNotifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function read(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) return response()->json(['error' => 'Not a vendor'], 403);

        if (str_starts_with($id, 'global_')) {
            $announcementId = str_replace('global_', '', $id);
            \App\Models\VendorAnnouncementRead::firstOrCreate([
                'vendor_id' => $vendor->id,
                'vendor_announcement_id' => $announcementId
            ]);
            return response()->json(['message' => 'Announcement marked as read']);
        }

        $notification = $vendor->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 404);
        }

        $vendor->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) return response()->json(['error' => 'Not a vendor'], 403);

        if (str_starts_with($id, 'global_')) {
            return response()->json(['error' => 'Global announcements cannot be deleted'], 403);
        }

        $notification = $vendor->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->delete();
            return response()->json(['message' => 'Notification deleted']);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }
}
