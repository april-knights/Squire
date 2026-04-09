<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Notification;

class NotificationApiController extends Controller
{
    /**
     * GET /api/notifications/pending
     * Returns all notifications not yet delivered to Discord,
     * where the knight has a discordid to deliver to.
     */
    public function pending()
    {
        $notifications = Notification::pendingDiscord()
            ->join('knight', 'knight.pkey', '=', 'notification.fkeyknight')
            ->whereNotNull('knight.discordid')
            ->where('knight.activeflg', 1)
            ->where('knight.delflg', 0)
            ->select(
                'notification.pkey',
                'notification.fkeyknight',
                'notification.type',
                'notification.message',
                'notification.url',
                'notification.crtsetdt',
                'knight.discordid'
            )
            ->orderBy('notification.crtsetdt')
            ->limit(100)
            ->get();

        return response()->json([
            'count'         => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * POST /api/notification/{id}/sent
     * Marks a notification as delivered to Discord.
     */
    public function markSent(int $id)
    {
        $notification = Notification::where('pkey', $id)
            ->where('delflg', 0)
            ->firstOrFail();

        $notification->delivered_to_discord = 1;
        $notification->save();

        return response()->json(['ok' => true]);
    }
}