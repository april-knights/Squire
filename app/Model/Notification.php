<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Do NOT extend SquireModel / use HasActiveTrait.
 * activeflg has no meaning for notifications — visibility is delflg only.
 */
class Notification extends Model
{
    protected $table      = 'notification';
    protected $primaryKey = 'pkey';

    const CREATED_AT = 'crtsetdt';
    const UPDATED_AT = null;

    protected $fillable = [
        'fkeyknight',
        'type',
        'message',
        'url',
        'crtsetid',
    ];

    protected $casts = [
        'read_at'              => 'datetime',
        'delivered_to_discord' => 'boolean',
        'activeflg'            => 'boolean',
        'delflg'               => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')->where('delflg', 0);
    }

    public function scopePendingDiscord($query)
    {
        return $query->where('delivered_to_discord', 0)->where('notification.delflg', 0);
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    // ---------------------------------------------------------------
    // Static dispatch — fan-out helper used by NotificationService
    // ---------------------------------------------------------------

    /**
     * Insert notifications in bulk for a set of knight pkeys.
     *
     * @param int[]       $knightIds
     * @param string      $type
     * @param string      $message
     * @param string|null $url
     * @param int         $actorId    Knight pkey triggering the event (crtsetid)
     */
    public static function dispatch(
        array $knightIds,
        string $type,
        string $message,
        ?string $url,
        int $actorId
    ): void {
        if (empty($knightIds)) {
            return;
        }

        $now  = now();
        $rows = array_map(fn($id) => [
            'fkeyknight' => $id,
            'type'       => $type,
            'message'    => $message,
            'url'        => $url,
            'crtsetid'   => $actorId,
            'crtsetdt'   => $now,
        ], $knightIds);

        // Chunk to avoid hitting MySQL max_allowed_packet on large rosters
        foreach (array_chunk($rows, 500) as $chunk) {
            \DB::table('notification')->insert($chunk);
        }
    }
}