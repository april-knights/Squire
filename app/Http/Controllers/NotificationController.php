<?php

namespace App\Http\Controllers;

use App\Model\Notification;
use Illuminate\Http\Request;
use Auth;

class NotificationController extends Controller
{
    /**
     * GET /notifications
     * Returns unread notifications as JSON for the navbar bell dropdown.
     */
    public function index()
    {
        $notifications = Notification::unread()
            ->where('fkeyknight', Auth::id())
            ->orderBy('crtsetdt', 'desc')
            ->limit(15)
            ->get(['pkey', 'type', 'message', 'url', 'crtsetdt']);

        $unreadCount = Notification::unread()
            ->where('fkeyknight', Auth::id())
            ->count();

        return response()->json([
            'count'         => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * POST /notifications/{id}/read
     * Marks a single notification as read and redirects to its URL.
     */
    public function markRead(int $id)
    {
        $notification = Notification::where('pkey', $id)
            ->where('fkeyknight', Auth::id())
            ->where('delflg', 0)
            ->firstOrFail();

        $notification->read_at = now();
        $notification->save();

        return redirect($notification->url ?? '/');
    }

    /**
     * POST /notifications/read-all
     * Marks all unread notifications as read for the current knight.
     */
    public function markAllRead()
    {
        Notification::unread()
            ->where('fkeyknight', Auth::id())
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}