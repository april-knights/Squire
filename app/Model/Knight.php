<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Log;
use DB;

class Knight extends Authenticatable
{
    use Notifiable;

    protected $table = 'knight';
    protected $primaryKey = 'pkey';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rname', 'dname', 'bio', 'firstevent', 'frenemy', 'rlimpact', 'delflg',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'crtsetdt' => 'datetime',
        'lstmdts' => 'datetime',
    ];

    /**
     * Whether this knight is a member of a battalion.
     *
     * @var int battalion primary key
     *
     * @return bool
     */
    public function isBattMember($batt_key) {
        $myid = $this->getAuthIdentifier();
        return in_array($batt_key, get_object_vars(DB::select('SELECT batt, batt2 FROM knight WHERE pkey = ?', [$myid])[0]));
    }

    /**
     * Get this knight's rank value.
     *
     * @return int
     */
    public function getRankVal() {
        $myid = $this->getAuthIdentifier();
        $rank = DB::select('SELECT r.rval FROM knight k
                            INNER JOIN krank r ON r.pkey = k.rnk
                            WHERE k.pkey = ?', [$myid])[0] ?? null;

        if ($rank) {
            return $rank->rval;
        } else {
            return 99; # If the knight doesn't have an assigned rank, default to rval 99.
        }
    }

    /**
     * Get this knight's rank name.
     *
     * @return int
     */
    public function getRankName() {
        $myid = $this->getAuthIdentifier();
        $rank = DB::select('SELECT r.name FROM knight k
                            INNER JOIN krank r ON r.pkey = k.rnk
                            WHERE k.pkey = ?', [$myid])[0] ?? null;

        if ($rank) {
            return $rank->name;
        } else {
            return ''; # If the knight doesn't have an assigned rank, return empty string.
        }
    }

    /**
     * Whether this knight is a councillor.
     *
     * @return bool
     */
    public function isCouncillor() {
        // If the rank value is lower than or equal to 3, this knight is a councillor
        return $this->getRankVal() <= 3;
    }

    /**
     * Whether this knight is an officer of a certain battalion.
     *
     * @var int battalion primary key
     *
     * @return bool
     */
    public function isOfficer($batt_key) {
        // If the rank value is lower than or equal to 8, this knight is an officer
        return $this->isBattMember($batt_key) && $this->getRankVal() <= 8;
    }

    /**
     * Check a users security entry for the given key.
     *
     * @param string $key   Security entry to check. Never use user input here.
     * @return bool
     */
    public function checkSecurity($key) {
        $security = $this->security()->{$key};
        if (!$security) {
            return false; # Knight has no linked security entry
        } else {
            return current((array) $security) == 1;
        }
    }

    public function security(): HasOne {
        return $this->hasOne(Security::class);
    }

    public function rank(): HasOne {
        return $this->hasOne(Rank::class, 'pkey', 'rnk');
    }

    public function battalion(): BelongsTo {
        return $this->belongsTo(Battalion::class, 'pkey', 'batt');
    }
}
