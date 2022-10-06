<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

class Knight extends SquireModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    use Notifiable;
    use HasActiveTrait;

    protected $table = 'knight';
    protected static string|null $permName = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'knum', 'rname', 'dname', 'email', 'bio', 'firstevent', 'rlimpact', 'batt', 'rnk', 'security', 'crtsetid',
        'lstmdby', 'frenemy'
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
     * @var int $batt_key battalion primary key
     *
     * @return bool
     */
    public function isBattMember($batt_key) {
        return $this->battalion->pkey === $batt_key;
    }

    /**
     * Get this knight's rank value.
     *
     * @return int
     */
    public function getRankVal() {
        if ($this->rank) {
            return $this->rank->rval;
        } else {
            return Rank::DEFAULT_KNIGHT_RANK; # If the knight doesn't have an assigned rank, default to rval 99.
        }
    }

    /**
     * Get this knight's rank name.
     *
     * @return int
     */
    public function getRankName() {
        if ($this->rank) {
            return $this->rank->name;
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
        return $this->getRankVal() <= Rank::HIGHEST_COUNCILOR_RANK;
    }

    /**
     * Whether this knight is an officer of a certain battalion.
     *
     * @var int $batt_key battalion primary key
     *
     * @return bool
     */
    public function isOfficer($batt_key) {
        // If the rank value is lower than or equal to 8, this knight is an officer
        return $this->isBattMember($batt_key) && $this->getRankVal() <= Rank::HIGHEST_OFFICER_RANK;
    }

    /**
     * Check a users security entry for the given key.
     *
     * @param string $key   Security entry to check. Never use user input here.
     * @return bool
     */
    public function checkSecurity($key) {
        $security = $this->security()->first();
        if (!$security) {
            return false; # Knight has no linked security entry
        } else {
            return $security->{$key};
        }
    }

    public function security(): HasOne {
        return $this->hasOne(Security::class, 'pkey', 'security');
    }

    public function rank(): HasOne {
        return $this->hasOne(Rank::class, 'pkey', 'rnk');
    }

    public function battalion(): BelongsTo {
        return $this->belongsTo(Battalion::class, 'batt', 'pkey');
    }

    public function divisions(): BelongsToMany {
        return $this->belongsToMany(Division::class, 'divknight', 'fkeyknight', 'fkeydivision')
            ->withPivot('delflg')->using(DivKnight::class)->deleted(false);
    }

    public function firstEvent(): BelongsTo {
        return $this->belongsTo(Event::class, 'firstevent', 'pkey');
    }

    public function skills(): BelongsToMany {
        return $this->belongsToMany(Skill::class, 'userskill', 'fkeyuser', 'fkeyskill')
            ->withPivot('delflg')->deleted(false)->using(UserSkill::class);
    }
}
