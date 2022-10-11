<?php

namespace App\Model;

use App\Support\HasActiveTrait;
use App\Support\SquireModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends SquireModel {
    use HasActiveTrait;

    protected static string|null $permName = 'skill';
    protected $table = 'skill';

    protected $fillable = [
        'skillname',
        'skilldescr'
    ];

    public function users(?bool $deleted = false): BelongsToMany {
        return $this->checkDeletedPivot($this->belongsToMany(Knight::class, 'userskill', 'fkeyskill', 'fkeyuser')
            ->withPivot('delflg')->using(UserSkill::class), $deleted);
    }
}
