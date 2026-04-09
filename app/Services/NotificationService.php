<?php

namespace App\Services;

use App\Model\Knight;
use App\Model\Notification;
use App\Model\Order;
use App\Model\Rank;

class NotificationService
{
    /**
     * Fan out a "new order" notification to every knight who can see it.
     * Called from OrdersController::store() after the order is saved.
     */
    public static function notifyNewOrder(Order $order, int $actorId): void
    {
        $recipientIds = self::recipientsForOrder($order, $actorId);

        if (empty($recipientIds)) {
            return;
        }

        Notification::dispatch(
            knightIds: $recipientIds,
            type:      'new_order',
            message:   'New order posted: ' . $order->title,
            url:       '/orders',
            actorId:   $actorId,
        );
    }

    /**
     * Resolve the set of knight pkeys who should receive a notification
     * for this order, excluding the author.
     *
     * Uses withoutGlobalScopes() + manual activeflg/delflg conditions
     * to avoid ambiguous column errors from HasActiveTrait when joining
     * knight to krank.
     */
    private static function recipientsForOrder(Order $order, int $actorId): array
    {
        $query = Knight::withoutGlobalScopes()
            ->join('krank', 'krank.pkey', '=', 'knight.rnk')
            ->where('knight.activeflg', 1)
            ->where('knight.delflg', 0)
            ->where('knight.pkey', '!=', $actorId)
            ->select('knight.pkey');

        if ($order->level === 0) {
            // Battalion order — notify battalion members + Grandmaster (rnk = 1)
            $query->where(function ($q) use ($order) {
                $q->where('knight.batt', $order->fkeybattalion)
                  ->orWhere('knight.rnk', 1);
            });
        } elseif ($order->level <= Rank::HIGHEST_COMMANDER_RANK) {
            $query->where('krank.rval', '<=', Rank::HIGHEST_COMMANDER_RANK);
        } elseif ($order->level <= Rank::HIGHEST_OFFICER_RANK) {
            $query->where('krank.rval', '<=', Rank::HIGHEST_OFFICER_RANK);
        }
        // Knights tier — no rank filter, all active knights qualify

        return $query->pluck('knight.pkey')->all();
    }
}