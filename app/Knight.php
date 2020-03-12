<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        return DB::select('SELECT r.rval FROM knight k INNER JOIN krank r ON r.pkey = k.rnk WHERE k.pkey = ?', [$myid])[0]->rval;
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
}
