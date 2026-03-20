<?php

namespace App\Http\Controllers;

use App\Model\Battalion;
use App\Model\Division;
use App\Model\Event;
use App\Model\Knight;
use App\Model\Rank;
use App\Model\Security;
use App\Model\Skill;
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('knight.index', ['knights' => Knight::get()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\View\View
     */
public function show($rname)
{
    $knight = Knight::firstWhere('rname', $rname);
    if(!$knight) {
        abort(404, 'Knight not found.');
    }
    $editingSelf = Auth::id() == $knight->pkey;
    // Certain fields are limited to councillors and the user themselves
    $show_sensitive = Auth::user()->isCouncillor() || $editingSelf;
    // Other fields are limited to officers from this knight's battalion
    $show_irl = Auth::user()->isOfficer($knight->batt) || $editingSelf;
    // Discord ID and interview transcript visible to any officer+
    $show_officer_fields = Auth::user()->getRankVal() <= Rank::HIGHEST_OFFICER_RANK;
    // Officer notes restricted to councillors for anyone, officers for own battalion only
    $show_onote = Auth::user()->isCouncillor() || Auth::user()->isOfficer($knight->batt);
    // Editable by councillors for anyone, or officers for their own battalion
    $can_edit_officer_fields = Auth::user()->isCouncillor() ||
                               Auth::user()->isOfficer($knight->batt);
    return view('profile.show', [
        'knight' => $knight,
        'show_sensitive' => $show_sensitive,
        'show_irl' => $show_irl,
        'show_officer_fields' => $show_officer_fields,
        'show_onote' => $show_onote,
        'can_edit_officer_fields' => $can_edit_officer_fields,
        'can_edit' => $this->editableFields($knight) !== null,
    ]);
}
    /**
     * Show the form for creating a new resource.
     *
     * @param integer $def_batt Default battalion
     * @param integer $def_rank Default rank
     * @param integer $def_sec  Default security
     * @return \Illuminate\View\View
     */
public function create(Request $request)
{
    // $def_batt=99, $def_rank=13, $def_sec=9
    abort_if(!Auth::user()->checkSecurity('cmuser') && !Auth::user()->checkSecurity('cmbattuser'), 401, 'Not authorized to create knight.');

    $validated = $request->validate([
        'batt' => 'nullable|integer|exists:battalion,pkey',
        'rank' => 'nullable|integer|exists:krank,pkey',
        'security' => 'nullable|integer|exists:security,pkey',
    ]);

    // Compute next available knum
    // knum is CHAR(6), so we compute numeric max safely and then format back to 6 chars.
    // Baseline: ensure we never allocate below 100257 (i.e., floor at 100256)
    $baseline = 100256;

    $maxKnum = (int) Knight::query()
        ->whereRaw("knum REGEXP '^[0-9]{6}$'")
        ->selectRaw('MAX(CAST(knum AS UNSIGNED)) AS max_knum')
        ->value('max_knum');

    $next_knum_int = max($maxKnum, $baseline) + 1;
    $next_knum = str_pad((string) $next_knum_int, 6, '0', STR_PAD_LEFT);

    return view('profile.create', [
        'all_ranks' => Rank::get(),
        'all_skills' => Skill::get(),
        'all_batts' => Battalion::get(),
        'all_divs' => Division::get(),
        'all_events' => Event::get(),
        'all_secs' => Security::get(),
        'def_batt' => $validated['batt'] ?? Battalion::DEFAULT_BATTALION,
        'def_rank' => $validated['rank'] ?? Rank::DEFAULT_PROFILE_RANK_ID,
        'def_sec' => $validated['security'] ?? Security::DEFAULT_PROFILE_SECURITY_ID,
        'next_knum' => $next_knum,
        'is_commander' => !Auth::user()->isCouncillor(),
        'user_batt' => Auth::user()->batt,
    ]);
}


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\View\View
     */
    public function edit($rname)
    {
        $knight = Knight::firstWhere('rname', $rname);

        abort_if(!$knight, 404, 'Knight not found.');

        $editable_fields = self::editableFields($knight);
        abort_if(!$editable_fields, 401, 'Not authorized to edit knight.');

        return view('profile.edit', ['knight' => $knight,
                                     'cur_divs' => $knight->divisions,
                                     'cur_skills' => $knight->skills,
                                     'all_ranks' => Rank::get(),
                                     'all_secs' => Security::get(),
                                     'all_skills' => Skill::get(),
                                     'all_batts' => Battalion::get(),
                                     'all_divs' => Division::get(),
                                     'all_events' => Event::get(),
                                     'editable_fields' => $editable_fields,
                                     'can_delete' => Auth::user()->checkSecurity('cduser'),
                                    ]);
    }

    /**
     * Gets the profile fields editable by another user.
     *
     * @param Knight|null $knight Knight to be edited
     * @return array        Array of editable fields
     */
private static function editableFields(Knight $knight = null) {
    $user = Auth::user();

    // Councillor is editing the profile
    if ($user->checkSecurity(Knight::getPermission(Knight::PERMISSION_MODIFY))) {
        return array('rname', 'dname', 'email', 'batt', 'rank', 'security', 'divs', 'firstevent', 'skills', 'bio', 'rlimpact', 'discordid', 'inttrans', 'onote');
        // TODO: implement 'activeflg',
    // User is editing their own profile
    } elseif ($knight && $knight->pkey == $user->getAuthIdentifier()) {
        return array('dname', 'email', 'bio', 'rlimpact', 'skills');
    // Battalion officer is editing
    } elseif ($user->checkSecurity('cmbattuser') && $user->isBattMember($knight->batt)) {
        return array('discordid', 'inttrans', 'onote');
    } else {
        return null;
    }
}

    /**
     * Generate edit rules.
     *
     * @param array|null $fields Fields to include in rules, null for all
     * @param Knight|null $knight Knight being edited, null if being created
     * @param int $min_sec  Minimum security level
     * @return array        Array of rules
     */
    private static function getRules(array $fields = null, Knight $knight = null, int $min_sec = 0) {
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
                $unique,
            ],
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('knight')->ignore($knight ? $knight->rname : null, 'rname')->whereNotNull('email')->where('email', '!=', ''),
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
            'discordid' => 'nullable|string|max:30',
            'inttrans' => 'nullable|url|max:255',
            'onote' => 'nullable|string|max:1000',
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) {    
if (!Auth::user()->checkSecurity(Knight::getPermission(Knight::PERMISSION_MODIFY)) && !Auth::user()->checkSecurity('cmbattuser')) {
    Log::warning('User ' . Auth::user()->rname . ' illegally attempted to create user!');
    abort(401, 'You are not authorized to create a knight!');
}

        $request->merge(['rname' => preg_replace('/^\/u\//', '', $request->input('rname'))]);
        $validated = $request->validate($this->getRules());
        // Prevent unauthorised users from adding Inquisition membership on creation
        if (!Auth::user()->canManageInquisition()) {
            if (in_array(5, $validated['divs'] ?? [])) {
                abort(401, 'Not authorized to add Inquisition membership.');
            }
        }

// Commander - force battalion, rank and security to defaults
if (!Auth::user()->isCouncillor()) {
    $validated['batt'] = Auth::user()->batt;
    $validated['rank'] = Rank::DEFAULT_PROFILE_RANK_ID;
    $validated['security'] = Security::DEFAULT_PROFILE_SECURITY_ID;
}

        // Start transaction
        DB::transaction(function () use (&$validated) {
            $editor = Auth::id();

            // Create knight
            $knight = new Knight([
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
            ]);
            $knight->save();

            // Set skills
            if(array_key_exists('skills', $validated)) {
                foreach ($validated['skills'] as $skill) {
                    $knight->skills()->attach($skill, ['crtsetid' => $editor, 'lstmdby' => $editor]);
                }
            }

            // Set divisions
            if(array_key_exists('divs', $validated)) {
                foreach ($validated['divs'] as $div) {
                    $knight->divisions()->attach($div, ['crtsetid' => $editor, 'lstmdby' => $editor]);
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $rname)
    {
        $knight = Knight::firstWhere('rname', $rname);
        abort_if(!$knight, 404, 'Knight not found.');
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

        $request->merge(['rname' => preg_replace('/^\/u\//', '', $request->input('rname'))]);
        
        $validator = Validator::make(
            $request->all(),
            $this->getRules($editable_fields, $knight, $min_sec),
            $this->getMessages()
        );

        $validated = $validator->validate();
        // Prevent unauthorised users from modifying Inquisition membership
        if (!Auth::user()->canManageInquisition()) {
            $currentInquisition = $knight->divisions->contains('pkey', 5);
            $newInquisition = in_array(5, $validated['divs'] ?? []);
            if ($currentInquisition !== $newInquisition) {
                abort(401, 'Not authorized to modify Inquisition membership.');
            }
        }

        // Start transaction
        DB::transaction(function () use (&$validated, &$rname, &$knight) {

            $editor = Auth::id();

            // Update skills
            $knight->syncRelation('skills', $validated['skills'] ?? []);

            // Update divisions
            $knight->syncRelation('divisions', $validated['divs'] ?? []);


            // Update knight, using old values if not set.
            $knight->fill([
                'rname' => $validated['rname'] ?? $knight->rname,
                'dname' => $validated['dname'] ?? $knight->dname,
                'email' => $validated['email'] ?? $knight->email,
                'bio' => $validated['bio'] ?? $knight->bio,
                'firstevent' => $validated['firstevent'] ?? $knight->firstevent,
                'rlimpact' => $validated['rlimpact'] ?? $knight->rlimpact,
                'discordid' => $validated['discordid'] ?? $knight->discordid,
                'inttrans' => $validated['inttrans'] ?? $knight->inttrans,
                'onote' => $validated['onote'] ?? $knight->onote,
                'batt' => $validated['batt'] ?? $knight->batt,
                'rnk' => $validated['rank'] ?? $knight->rnk,
                'security' => $validated['security'] ?? $knight->security,
                'crtsetid' => $editor,
                'lstmdby' => $editor,
            ])->save();
        // Commit
        });

        return redirect()->route('profile', ['rname' => $validated['rname'] ?? $rname]);
    }

    /**
     * Set delete flag for user.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $rname)
    {
        if (!Auth::user()->checkSecurity(Knight::getPermission(Knight::PERMISSION_DELETE))) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to delete user ' . $rname . '!');
            abort(401, 'You are not authorized to delete that knight!');
        }

        $knight = Knight::firstWhere('rname', $rname);

        if (!$knight) {
            abort(404, 'Knight does not exist!');
        }

        $knight->delflg = true;
        $knight->save();

        $request->session()->flash('success', 'Deleted user ' . $rname . '.');

        return redirect()->route('home');
    }
}
