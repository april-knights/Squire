<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Log;
use Auth;
use App\Knight;
use App\Http\Requests\StoreProfile;

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
                              WHERE u.fkeyuser = ? AND s.delflg = 0 AND u.delflg = 0', [$knight->pkey]);

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

        if (!$knight || $knight->delflg == 1) {
            abort(404, 'Knight not found.');
        }

        $editable_fields = self::editableFields($knight, Auth::user());

        if (!$editable_fields) {
            abort(401, 'Not authorized to edit knight.');
        }

        $cur_skills = DB::select('SELECT s.pkey, s.skillname, s.parentid FROM skill s
                                  INNER JOIN userskill u ON s.pkey = u.fkeyskill
                                  WHERE u.fkeyuser = ? AND s.delflg = 0 AND u.delflg = 0', [$knight->pkey]);

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

        $all_events = DB::select('SELECT pkey, title FROM event');


        return view('profile.edit', ['knight' => $knight,
                                     'cur_divs' => $cur_divs,
                                     'cur_skills' => $cur_skills,
                                     'all_ranks' => $all_ranks,
                                     'all_skills' => $all_skills,
                                     'all_batts' => $all_batts,
                                     'all_divs' => $all_divs,
                                     'all_events' => $all_events,
                                     'editable_fields' => $editable_fields, // TODO: Deactivate non-editable in blade
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProfile $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $rname
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProfile $request, $rname)
    {
        $validated = $request->validated();

        // Start transaction
        DB::transaction(function () use (&$validated, &$rname) {

            $editor = Auth::id();
            // We need to get the pkey incase we change rname later
            $pkey = DB::select('SELECT pkey FROM knight WHERE rname = ?', [$rname])[0]->pkey ?? null;

            if (!$pkey) {
                Log::error("Could not get pkey for rname " . $rname);
                abort(503);
            }


            // Update skills
            if(array_key_exists('skills', $validated)) {
                $old_skills_obj = DB::select('SELECT fkeyskill FROM userskill
                                              WHERE fkeyuser = ? AND delflg = 0', [$pkey]);

                // Merge array of skill objects into a single array of skill pkeys
                $old_skills = [];
                foreach($old_skills_obj as $skill) array_push($old_skills, $skill->fkeyskill);

                // Get deleted and added skills by array intersection
                $deleted = array_diff($old_skills, $validated['skills']);
                $added = array_diff($validated['skills'], $old_skills);

                // Set delflag for deleted skills
                foreach ($deleted as $skill) {
                    DB::update('UPDATE userskill
                                SET delflg = 1,
                                    lstmdby = :editor,
                                    lstmdts = CURRENT_TIMESTAMP
                                WHERE fkeyuser = :userid AND fkeyskill = :skillid',
                                ['userid' => $pkey, 'skillid' => $skill, 'editor' => $editor]);
                }

                // Add skills, reactive deleted ones if they exist
                foreach ($added as $skill) {
                    $prev_deleted = DB::select('SELECT usid FROM userskill
                                                WHERE fkeyuser = ? AND fkeyskill = ? AND delflg = 1', [$pkey, $skill])[0]->usid ?? null;

                    if ($prev_deleted) {
                        DB::update('UPDATE userskill
                                    SET delflg = 0,
                                        lstmdby = :editor,
                                        lstmdts = CURRENT_TIMESTAMP
                                    WHERE usid = :usid',
                                    ['editor' => $editor, 'usid' => $prev_deleted]);
                    } else {
                        DB::insert('INSERT INTO userskill (fkeyuser, fkeyskill, crtsetid, lstmdby)
                                    VALUES (?, ?, ?, ?)', [$pkey, $skill, $editor, $editor]);
                    }
                }
            }


            // Update knight-editable values
            DB::update('UPDATE knight
                        SET dname = ?,
                            email = ?,
                            bio = ?,
                            firstevent = ?,
                            rlimpact = ?,
                            lstmdby = ?,
                            lstmdts = CURRENT_TIMESTAMP
                        WHERE pkey = ?',
                        [$validated['dname'], $validated['email'],$validated['bio'],
                         $validated['firstevent'], $validated['rlimpact'], $editor, $pkey
                        ]);
        // Commit
        });

        return redirect()->route('profile', ['rname' => $rname]);
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
