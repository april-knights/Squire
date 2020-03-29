<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use App\Knight;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $knights = DB::select('select * from knights');

        return view('knight.index', ['knights' => $knights]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function show($rname)
    {
        $knight = DB::select('SELECT * FROM knight WHERE rname = ?', [$rname])[0] ?? null;

        if(!$knight) {
            abort(404, 'Knight not found.');
        }

        $rank = DB::select('SELECT name, rankdescr FROM krank
                            WHERE pkey = ?', [$knight->rnk])[0] ?? null;

        $batt = DB::select('SELECT name, battalias FROM battalion
                            WHERE pkey = ?', [$knight->batt])[0] ?? null;

        $skills = DB::select('SELECT s.skillname FROM skill s
                              INNER JOIN userskill u ON s.pkey = u.fkeyskill
                              WHERE u.fkeyuser = ? AND s.delflg = 0', [$knight->pkey]);

        // TODO: Show skill parents as well
        $divs = DB::select('SELECT * FROM knight k
                            INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                            INNER JOIN division d ON d.pkey = dk.fkeydivision
                            WHERE d.delflg = 0 AND k.rname = ?', [$rname]);

        // Certain fields are limited to councillors and the user themselves
        $show_sensitive = (Auth::user()->isCouncillor()) || (Auth::id() == $knight->pkey);

        // Other fields are limited to officers from this knight's battalion
        $show_irl = (Auth::user()->isOfficer($knight->batt)) || (Auth::id() == $knight->pkey);

        return view('profile.show', ['knight' => $knight,
                                     'rank' => $rank,
                                     'batt' => $batt,
                                     'divs' => $divs,
                                     'skills' => $skills,
                                     'show_sensitive' => $show_sensitive,
                                     'show_irl' => $show_irl,
                                     ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function edit($rname)
    {
        $knight = DB::select('SELECT * FROM knight WHERE rname = ?', [$rname])[0] ?? null;

        if (!$knight) {
            abort(404, 'Knight not found.');
        }

        $editable_fields = self::editableFields($knight, Auth::user());

        if (!$editable_fields) {
            abort(401, 'Not authorized to edit knight.');
        }

        $cur_rank = $knight->rnk;
        $cur_batt = $knight->batt;
        $cur_skills = DB::select('SELECT s.pkey, s.skillname, s.parentid FROM skill s
                                  INNER JOIN userskill u ON s.pkey = u.fkeyskill
                                  WHERE u.fkeyuser = ? AND s.delflg = 0', [$knight->pkey]);

        $cur_divs = DB::select('SELECT d.pkey, d.name FROM knight k
                                INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                                INNER JOIN division d ON d.pkey = dk.fkeydivision
                                WHERE d.delflg = 0 AND k.rname = ?', [$rname]);

        $all_ranks = DB::select('SELECT pkey, name FROM krank
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_skills = DB::select('SELECT pkey, skillname, parentid FROM skill
                                  WHERE activeflg = 1 AND delflg = 0');

        $all_batts = DB::select('SELECT pkey, name FROM battalion
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_divs = DB::select('SELECT pkey, name FROM division
                                WHERE activeflg = 1 AND delflg = 0');

        return view('profile.edit', ['knight' => $knight,
                                     'cur_rank' => $cur_rank,
                                     'cur_batt' => $cur_batt,
                                     'cur_divs' => $cur_divs,
                                     'cur_skills' => $cur_skills,
                                     'all_ranks' => $all_ranks,
                                     'all_skills' => $all_skills,
                                     'all_batts' => $all_batts,
                                     'all_divs' => $all_divs,
                                     'editable_fields' => $editable_fields,
                                     ]);
    }

    /**
     * Gets the profile fields editable by another user.
     *
     * @param array    $knight Knight to be edited.
     * @param Knight    $user User doing the editing.
     * @return array    Array of editable fields
     */
    private static function editableFields($knight, $user) {
        # Councillor is editing the profile
        if ($user->checkSecurity('cmuser')) {
            return array('rname', 'dname', 'email', 'bio', 'firstevent', 'rlimpact', 'batt', 'divs', 'batt2', 'rnk', 'security', 'activeflg', 'skills');
        # Battalion officer is editing
        } elseif ($user->checkSecurity('cmbattuser') && $user->isBattMember($knight->batt)) {
            return null;
        # User is editing their own profile
        } elseif ($knight->pkey == $user->getAuthIdentifier()) {
            return array('dname', 'email', 'bio', 'firstevent', 'rlimpact', 'skills');
        } else {
            return null;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modified = false;

        // Transaction

        // Update skills?

        // Update everything else

        if ($modified) {
            // Update modified timestamp/user
        }

        // Commit
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
