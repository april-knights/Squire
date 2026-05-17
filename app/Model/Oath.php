<?php

namespace app\Model;

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
    ];

    protected $casts = [
        'verified'    => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function knight()
    {
        return $this->belongsTo(Knight::class, 'fkeyknight', 'pkey');
    }

    /**
     * Get the oath record for a knight for the current oath cycle year.
     * Uses the oath thread with the youngest crtsetdt in settings
     * rather than calendar year, so a late thread doesn't break eligibility.
     */
    public static function currentYearForKnight(int $knightPkey): ?self
    {
        $year = static::currentOathYear();
        return static::where('fkeyknight', $knightPkey)
            ->where('oath_year', $year)
            ->first();
    }

    /**
     * Resolve the current oath year from the configured oath thread timestamp.
     * Falls back to current calendar year if no thread is configured.
     */
    public static function currentOathYear(): int
    {
        $threadDate = Setting::get('oath_thread_crtsetdt');
        if ($threadDate) {
            return (int) date('Y', strtotime($threadDate));
        }
        return (int) date('Y');
    }
}