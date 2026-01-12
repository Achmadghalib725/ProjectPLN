<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for current user (for AJAX/API)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->appNotifications()
            ->with('suratJalan:id,nomor,status')
            ->take(20)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread count only (for polling/badge update)
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = AppNotification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $shouldRedirect = $request->boolean('redirect', false);

        // Redirect to notification URL only when requested
        if ($shouldRedirect && $notification->url) {
            return redirect($notification->url);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $notification = AppNotification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus.');
    }

    /**
     * Clear all notifications
     */
    public function clearAll(Request $request)
    {
        Auth::user()->appNotifications()->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi dihapus.');
    }
}
