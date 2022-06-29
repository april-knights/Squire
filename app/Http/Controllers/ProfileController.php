<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use DB;
use Log;
use Auth;
use Validator;

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
        $knight = DB::select('SELECT * FROM knight
                              WHERE delflg = 0 AND rname = ?',
                             [$rname])[0] ?? null;

        if(!$knight) {
            abort(404, 'Knight not found.');
        }

        $rank = DB::select('SELECT name, rankdescr FROM krank
                            WHERE pkey = ?', [$knight->rnk])[0] ?? null;

        $batt = DB::select('SELECT name, battalias FROM battalion
                            WHERE pkey = ?', [$knight->batt])[0] ?? null;

        $skills = DB::select('SELECT s.skillname FROM skill s
                              INNER JOIN userskill u ON s.pkey = u.fkeyskill
                              WHERE s.delflg = 0 AND u.delflg = 0 AND u.fkeyuser = ?', [$knight->pkey]);
        // TODO: Show skill parents as well

        $divs = DB::select('SELECT * FROM knight k
                            INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                            INNER JOIN division d ON d.pkey = dk.fkeydivision
                            WHERE d.delflg = 0 AND dk.delflg = 0 AND k.rname = ?', [$rname]);

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
                                     'can_edit' => $this->editableFields($knight) !== null,
                                    ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param integer $def_batt Default battalion
     * @param integer $def_rank Default rank
     * @param integer $def_sec  Default security
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // $def_batt=99, $def_rank=13, $def_sec=9
        abort_if(!Auth::user()->checkSecurity('cmuser'), 401, 'Not authorized to create knight.');

        $validated = $request->validate([
            'batt' => 'nullable|integer|exists:battalion,pkey',
            'rank' => 'nullable|integer|exists:krank,pkey',
            'security' => 'nullable|integer|exists:security,pkey',
        ]);

        $all_ranks = DB::select('SELECT pkey, name, rankdescr FROM krank
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_skills = DB::select('SELECT pkey, skillname, parentid FROM skill
                                  WHERE activeflg = 1 AND delflg = 0');

        $all_batts = DB::select('SELECT pkey, name, battdescr FROM battalion
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_divs = DB::select('SELECT pkey, name, divdescr FROM division
                                WHERE activeflg = 1 AND delflg = 0');

        $all_events = DB::select('SELECT pkey, title FROM event');

        $all_secs = DB::select('SELECT pkey, secname, secdescr FROM security
                                WHERE activeflg = 1 AND delflg = 0');

        return view('profile.create', [
            'all_ranks' => $all_ranks,
            'all_skills' => $all_skills,
            'all_batts' => $all_batts,
            'all_divs' => $all_divs,
            'all_events' => $all_events,
            'all_secs' => $all_secs,
            'def_batt' => $validated['batt'] ?? 99,
            'def_rank' => $validated['rank'] ?? 13,
            'def_sec' => $validated['security'] ?? 9,
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

        abort_if(!$knight || $knight->delflg == 1, 404, 'Knight not found.');

        $editable_fields = self::editableFields($knight);
        abort_if(!$editable_fields, 401, 'Not authorized to edit knight.');

        $cur_skills = DB::select('SELECT s.pkey, s.skillname, s.parentid FROM skill s
                                  INNER JOIN userskill u ON s.pkey = u.fkeyskill
                                  WHERE s.delflg = 0 AND u.delflg = 0 AND u.fkeyuser = ?', [$knight->pkey]);

        $cur_divs = DB::select('SELECT d.pkey, d.name, d.divdescr FROM knight k
                                INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                                INNER JOIN division d ON d.pkey = dk.fkeydivision
                                WHERE d.delflg = 0 AND dk.delflg = 0 AND k.rname = ?', [$rname]);

        $all_ranks = DB::select('SELECT pkey, name, rankdescr FROM krank
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_secs = DB::select('SELECT pkey, secname, secdescr FROM security
                                WHERE activeflg = 1 AND delflg = 0');

        $all_skills = DB::select('SELECT pkey, skillname, parentid FROM skill
                                  WHERE activeflg = 1 AND delflg = 0');

        $all_batts = DB::select('SELECT pkey, name, battdescr FROM battalion
                                 WHERE activeflg = 1 AND delflg = 0');

        $all_divs = DB::select('SELECT pkey, name, divdescr FROM division
                                WHERE activeflg = 1 AND delflg = 0');

        $all_events = DB::select('SELECT pkey, title FROM event');

        $can_delete = Auth::user()->checkSecurity('cduser');

        return view('profile.edit', ['knight' => $knight,
                                     'cur_divs' => $cur_divs,
                                     'cur_skills' => $cur_skills,
                                     'all_ranks' => $all_ranks,
                                     'all_secs' => $all_secs,
                                     'all_skills' => $all_skills,
                                     'all_batts' => $all_batts,
                                     'all_divs' => $all_divs,
                                     'all_events' => $all_events,
                                     'editable_fields' => $editable_fields,
                                     'can_delete' => $can_delete,
                                    ]);
    }

    /**
     * Gets the profile fields editable by another user.
     *
     * @param array $knight Knight to be edited
     * @return array        Array of editable fields
     */
    private static function editableFields($knight = null) {
        $user = Auth::user();

        // Councillor is editing the profile
        if ($user->checkSecurity('cmuser')) {
            return array('rname', 'dname', 'email', 'batt', 'rank', 'security', 'divs', 'firstevent', 'skills', 'bio', 'rlimpact');
            // TODO: implement 'batt2', 'activeflg',
        // User is editing their own profile
        } elseif ($knight && $knight->pkey == $user->getAuthIdentifier()) {
            return array('dname', 'email', 'bio', 'rlimpact', 'skills');
        // Battalion officer is editing
        } elseif ($user->checkSecurity('cmbattuser') && $user->isBattMember($knight->batt)) {
            return null;
        } else {
            return null;
        }
    }

    /**
     * Generate edit rules.
     *
     * @param array $fields Fields to include in rules, null for all
     * @param array $knight Knight being edited, null if being created
     * @param int $min_sec  Minimum security level
     * @return array        Array of rules
     */
    private static function getRules($fields = null, $knight = null, $min_sec = 0) {
        if ($knight) {
            $unique = Rule::unique('knight')->ignore($knight->rname, 'rname');
        } else {
            $unique = Rule::unique('knight');
        }

        $all_rules = [
            'knum' => [
                'required',
                'digits:6',
                $unique,
            ],
            'rname' => [
                'required',
                'max:30',
                $unique,
            ],
            'dname' => [
                'nullable',
                'max:40',
                'regex:/^.*#\d{4}/',
                $unique,
            ],
            'email' => [
                'nullable',
                'email',
                'max:50',
                $unique,
            ],
            'batt' => 'required|integer|exists:battalion,pkey',
            'rank' => 'required|integer|exists:krank,pkey',
            'security' => [
                'required',
                'integer',
                'exists:security,pkey',
                'min:' . $min_sec
            ],
            'divs' => 'nullable',
            'divs.*' => 'integer|exists:division,pkey',
            'firstevent' => 'nullable|integer|exists:event,pkey',
            'skills' => 'nullable',
            'skills.*' => 'integer|exists:skill,pkey', // TODO: Exclude parent skills.
            'bio' => 'nullable|string|max:255',
            'rlimpact' => 'nullable|string|max:255',
        ];

        if ($fields) {
            // Intersect values from editableFields with all rules
            return array_intersect_key($all_rules, array_flip($fields));
        } else {
            return $all_rules;
        }
    }

    /**
     * Generate validation messages
     *
     * @return array Array of messages
     */
    private static function getMessages() {
        return [
            'security.min' => 'You cannot set a security level higher than your own!',
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->checkSecurity('cmuser')) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to create user!');
            abort(401, 'You are not authorized to create a knight!');
        }

        $validated = $request->validate($this->getRules());

        // Start transaction
        DB::transaction(function () use (&$validated) {
            $editor = Auth::id();

            // Create knight
            /* I've tried to use raw SQL queries for maintainability by people without
             * PHP/Laravel knowledge but doing that here just made the code
             * not only unreadable but also much more complicated.
             */
            DB::table('knight')->insertGetId([
                'knum' => $validated['knum'],
                'rname' => $validated['rname'],
                'dname' => $validated['dname'],
                'email' => $validated['email'],
                'bio' => $validated['bio'],
                'firstevent' => $validated['firstevent'] ?? null,
                'rlimpact' => $validated['rlimpact'],
                'batt' => $validated['batt'],
                'rnk' => $validated['rank'],
                'security' => $validated['security'],
                'crtsetid' => $editor,
                'lstmdby' => $editor,
            ], 'pkey');

            // Get pkey for many-to-many relationships
            $pkey = DB::select('SELECT pkey FROM knight WHERE rname = ?', [$validated['rname']])[0]->pkey;

            // Set skills
            if(array_key_exists('skills', $validated)) {
                foreach ($validated['skills'] as $skill) {
                    DB::insert('INSERT INTO userskill (fkeyuser, fkeyskill, crtsetid, lstmdby)
                                VALUES (?, ?, ?, ?)', [$pkey, $skill, $editor, $editor]);
                }
            }

            // Set divisions
            if(array_key_exists('divs', $validated)) {
                foreach ($validated['divs'] as $div) {
                    DB::insert('INSERT INTO divknight (fkeyknight, fkeydivision, crtsetid, lstmdby)
                                VALUES (?, ?, ?, ?)', [$pkey, $div, $editor, $editor]);
                }
            }
        // Commit
        });

        return redirect()->route('profile', ['rname' => $validated['rname']]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $rname
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $rname)
    {
        $knight = DB::select('SELECT * FROM knight WHERE rname = ?', [$rname])[0] ?? null;
        $editable_fields = $this->editableFields($knight);

        if (!$editable_fields) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to edit user ' . $rname . '!');
            abort(401, 'Not authorized to edit knight.');
        }

        if ($knight->pkey == Auth::id()) {
            // Editing own profile, allow setting current security level
            $min_sec = Auth::user()->security;
        } else {
            // Only allow setting security levels up to one level higher than the editor's one
            $min_sec = Auth::user()->security + 1;
        }

        $validator = Validator::make(
            $request->all(),
            $this->getRules($editable_fields, $knight, $min_sec),
            $this->getMessages()
        );

        $validated = $validator->validate();

        // Start transaction
        DB::transaction(function () use (&$validated, &$rname, &$knight) {

            $editor = Auth::id();
            // We need to get the pkey incase we change rname later
            $pkey = DB::select('SELECT pkey FROM knight WHERE rname = ?', [$rname])[0]->pkey ?? null;

            if (!$pkey) {
                Log::error('Could not get pkey for rname ' . $rname);
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

                // Add skills, reactivate deleted ones if they exist
                foreach ($added as $skill) {
                    $prev_deleted = DB::select('SELECT usid FROM userskill
                                                WHERE fkeyuser = ? AND fkeyskill = ? AND delflg = 1',
                                                [$pkey, $skill])[0]->usid ?? null;

                    if ($prev_deleted) {
                        DB::update('UPDATE userskill
                                    SET delflg = 0,
                                        lstmdby = :editor,
                                        lstmdts = CURRENT_TIMESTAMP
                                    WHERE usid = :usid',
                                    ['editor' => $editor, 'usid' => $prev_deleted]);
                    } else {
                        DB::insert('INSERT INTO userskill (fkeyuser, fkeyskill, crtsetid, lstmdby)
                                    VALUES (?, ?, ?, ?)',
                                    [$pkey, $skill, $editor, $editor]);
                    }
                }
            }

            // Update divisions
            if(array_key_exists('divs', $validated)) {
                $old_divs_obj = DB::select('SELECT fkeydivision FROM divknight
                                            WHERE fkeyknight = ? AND delflg = 0', [$pkey]);

                // Merge array of div objects into a single array of div pkeys
                $old_divs = [];
                foreach($old_divs_obj as $div) array_push($old_divs, $div->fkeydivision);

                // Get deleted and added divs by array intersection
                $deleted = array_diff($old_divs, $validated['divs']);
                $added = array_diff($validated['divs'], $old_divs);

                // Set delflag for deleted divs
                foreach ($deleted as $div) {
                    DB::update('UPDATE divknight
                                SET delflg = 1,
                                    lstmdby = :editor,
                                    lstmdts = CURRENT_TIMESTAMP
                                WHERE fkeyknight = :userid AND fkeydivision = :divid',
                                ['userid' => $pkey, 'divid' => $div, 'editor' => $editor]);
                }

                // Add divs, reactivate deleted ones if they exist
                foreach ($added as $div) {
                    $prev_deleted = DB::select('SELECT pkey FROM divknight
                                                WHERE fkeyknight = ? AND fkeydivision = ? AND delflg = 1',
                                                [$pkey, $div])[0]->pkey ?? null;

                    if ($prev_deleted) {
                        DB::update('UPDATE divknight
                                    SET delflg = 0,
                                        lstmdby = :editor,
                                        lstmdts = CURRENT_TIMESTAMP
                                    WHERE pkey = :pkey',
                                    ['editor' => $editor, 'pkey' => $prev_deleted]);
                    } else {
                        DB::insert('INSERT INTO divknight (fkeyknight, fkeydivision, crtsetid, lstmdby)
                                    VALUES (?, ?, ?, ?)',
                                    [$pkey, $div, $editor, $editor]);
                    }
                }
            }


            // Update knight, using old values if not set.
            DB::table('knight')
                ->where('pkey', $pkey)
                ->update([
                    'rname' => $validated['rname'] ?? $knight->rname,
                    'dname' => $validated['dname'] ?? $knight->dname,
                    'email' => $validated['email'] ?? $knight->email,
                    'bio' => $validated['bio'] ?? $knight->bio,
                    'firstevent' => $validated['firstevent'] ?? $knight->firstevent,
                    'rlimpact' => $validated['rlimpact'] ?? $knight->rlimpact,
                    'batt' => $validated['batt'] ?? $knight->batt,
                    'rnk' => $validated['rank'] ?? $knight->rnk,
                    'security' => $validated['security'] ?? $knight->security,
                    'crtsetid' => $editor,
                    'lstmdby' => $editor,
                ]);
        // Commit
        });

        return redirect()->route('profile', ['rname' => $validated['rname'] ?? $rname]);
    }

    /**
     * Set delete flag for user.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $rname)
    {
        if (!Auth::user()->checkSecurity('cduser')) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to delete user ' . $rname . '!');
            abort(401, 'You are not authorized to delete that knight!');
        }

        $knight = DB::select('SELECT * FROM knight WHERE rname = ?', [$rname])[0] ?? null;

        if (!$knight) {
            abort(404, 'Knight does not exist!');
        }

        DB::table('knight')
                ->where('rname', $rname)
                ->update(['delflg' => 1]);

        $request->session()->flash('success', 'Deleted user ' . $rname . '.');

        return redirect()->route('home');
    }
}
