<?php

namespace App\Model;

use App\Support\SquireModel;

class Oath extends SquireModel
{
    protected $table = 'oath';

    protected $fillable = [
        'fkeyknight',
        'oath_year',
        'comment_url',
        'reddit_comment_id',
        'verified_at',
        'verified',
        'crtsetid',
        'lstmdby',
    ];

    protected $casts = [
        'verified'    => 'boolean',
        'verified_at' => 'datetime',
    ];

    /** Names must resolve even if the knight is later deactivated or deleted. */
    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey')->withoutGlobalScopes();
    }

    /**
     * The knight's oath record for the current oath cycle year.
     */
    public static function currentYearForKnight(int $knightPkey): ?self
    {
        return static::where('fkeyknight', $knightPkey)
            ->where('oath_year', static::currentOathYear())
            ->first();
    }

    /**
     * The oath cycle year: an explicit setting, changed only by an admin.
     */
    public static function currentOathYear(): int
    {
        $year = (int) Setting::get('oath_year');

        if ($year >= 2000) {
            return $year;
        }

        // Legacy fallback if the oath_year setting is missing
        $threadDate = Setting::get('oath_thread_crtsetdt');
        if ($threadDate) {
            return (int) date('Y', strtotime($threadDate));
        }

        return (int) date('Y');
    }
}