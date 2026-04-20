<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * All bit flag columns in display group order.
     */
    private const FLAG_GROUPS = [
        'Users'      => ['cvuser', 'cmuser', 'cduser'],
        'Skills'     => ['cvskill', 'cmskill', 'cdskill', 'cmsskill', 'cmoskill'],
        'Ranks'      => ['cvrank', 'cmrank', 'cdrank'],
        'Battalions' => ['cvbatt', 'cmbatt', 'cdbatt', 'cmbattuser'],
        'Events'     => ['cvevent', 'cmevent', 'cdevent'],
        'Security'   => ['cvsec', 'cmsec', 'cdsec'],
        'Orders'     => ['cmorder', 'cdorder'],
    ];

    /**
     * Human-readable labels for each flag column.
     */
    private const FLAG_LABELS = [
        'cvuser'     => 'View Users',
        'cmuser'     => 'Manage Users',
        'cduser'     => 'Delete Users',
        'cvskill'    => 'View Skills',
        'cmskill'    => 'Manage Skills',
        'cdskill'    => 'Delete Skills',
        'cmsskill'   => 'Manage Own Skills',
        'cmoskill'   => 'Manage Others\' Skills',
        'cvrank'     => 'View Ranks',
        'cmrank'     => 'Manage Ranks',
        'cdrank'     => 'Delete Ranks',
        'cvbatt'     => 'View Battalions',
        'cmbatt'     => 'Manage Battalions',
        'cdbatt'     => 'Delete Battalions',
        'cmbattuser' => 'Manage Battalion Members',
        'cvevent'    => 'View Events',
        'cmevent'    => 'Manage Events',
        'cdevent'    => 'Delete Events',
        'cvsec'      => 'View Security Profiles',
        'cmsec'      => 'Manage Security Profiles',
        'cdsec'      => 'Delete Security Profiles',
        'cmorder'    => 'Manage Orders',
        'cdorder'    => 'Delete Orders',
    ];

    /**
     * List all security profiles.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'pkey');
        $direction = $request->get('direction', 'asc');

        $allowed_sorts = ['pkey', 'secname', 'secdescr', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'pkey';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $profiles = DB::table('security')
            ->orderBy($sort, $direction)
            ->get();

        // Knight counts per profile
        $counts = DB::table('knight')
            ->select('security', DB::raw('COUNT(*) as cnt'))
            ->whereNull('delflg')
            ->orWhere('delflg', 0)
            ->groupBy('security')
            ->pluck('cnt', 'security');

        return view('admin.security.index', compact('profiles', 'counts', 'sort', 'direction'));
    }

    /**
     * Show a single security profile with its flag grid.
     */
     public function show($pkey)
     {
         $profile = DB::table('security')->where('pkey', $pkey)->first();
         if (!$profile) abort(404);

         $knight_count  = DB::table('knight')->where('security', $pkey)->where('delflg', 0)->count();
         $flag_groups   = self::FLAG_GROUPS;
         $flag_labels   = self::FLAG_LABELS;

         $lstmdby_name = null;
         if ($profile->lstmdby) {
             $lstmdby_name = Knight::withoutGlobalScopes()->where('pkey', $profile->lstmdby)->value('dname');
         }
         $crtsetid_name = null;
         if ($profile->crtsetid) {
             $crtsetid_name = Knight::withoutGlobalScopes()->where('pkey', $profile->crtsetid)->value('dname');
         }

         return view('admin.security.show', compact(
             'profile', 'knight_count', 'flag_groups', 'flag_labels', 'lstmdby_name', 'crtsetid_name'
         ));
     }

    /**
     * Show the create form.
     */
    public function create()
    {
        $flag_groups = self::FLAG_GROUPS;
        $flag_labels = self::FLAG_LABELS;
        return view('admin.security.create', compact('flag_groups', 'flag_labels'));
    }

    /**
     * Store a new security profile.
     */
    public function store(Request $request)
    {
        $request->validate([
            'secname'  => 'required|string|max:30',
            'secdescr' => 'nullable|string|max:255',
        ]);

        $nextPkey = DB::table('security')->max('pkey') + 1;
        $now      = now();
        $adminId  = auth()->user()->pkey;

        $data = [
            'pkey'     => $nextPkey,
            'secname'  => $request->input('secname'),
            'secdescr' => $request->input('secdescr'),
            'crtsetdt' => $now,
            'crtsetid' => $adminId,
            'lstmdby'  => $adminId,
            'lstmdts'  => $now,
            'activeflg' => 1,
            'delflg'   => 0,
        ];

        // Set each flag from checkbox — unchecked = 0
        foreach (array_merge(...array_values(self::FLAG_GROUPS)) as $flag) {
            $data[$flag] = $request->has($flag) ? 1 : 0;
        }

        DB::table('security')->insert($data);

        session()->flash('success', "Security profile '{$data['secname']}' created.");
        return redirect("/admin/security/{$nextPkey}");
    }

    /**
     * Show the edit form.
     */
    public function edit($pkey)
    {
        $profile     = DB::table('security')->where('pkey', $pkey)->first();
        if (!$profile) abort(404);

        $flag_groups = self::FLAG_GROUPS;
        $flag_labels = self::FLAG_LABELS;

        return view('admin.security.edit', compact('profile', 'flag_groups', 'flag_labels'));
    }

    /**
     * Update a security profile's name, description, and flags.
     */
    public function update(Request $request, $pkey)
    {
        $profile = DB::table('security')->where('pkey', $pkey)->first();
        if (!$profile) abort(404);

        $request->validate([
            'secname'  => 'required|string|max:30',
            'secdescr' => 'nullable|string|max:255',
        ]);

        $data = [
            'secname'  => $request->input('secname'),
            'secdescr' => $request->input('secdescr'),
            'lstmdby'  => auth()->user()->pkey,
            'lstmdts'  => now(),
        ];

        foreach (array_merge(...array_values(self::FLAG_GROUPS)) as $flag) {
            $data[$flag] = $request->has($flag) ? 1 : 0;
        }

        DB::table('security')->where('pkey', $pkey)->update($data);

        session()->flash('success', "Security profile updated.");
        return redirect("/admin/security/{$pkey}");
    }

    /**
     * Soft delete a profile — requires reassignment if knights are assigned.
     * POST /admin/security/{pkey}/delete
     * Optional: replacement_pkey for reassignment
     */
    public function destroy(Request $request, $pkey)
    {
        // Prevent deleting Admin (pkey=1) or Applicant (pkey=0)
        if (in_array($pkey, [0, 1])) {
            session()->flash('error', 'This profile cannot be deleted.');
            return redirect("/admin/security/{$pkey}");
        }

        $knight_count = DB::table('knight')->where('security', $pkey)->where('delflg', 0)->count();

        if ($knight_count > 0) {
            $replacement = $request->input('replacement_pkey');
            if (!$replacement && $replacement !== '0') {
                session()->flash('error', 'This profile has knights assigned. Select a replacement profile before deleting.');
                return redirect("/admin/security/{$pkey}");
            }

            // Reassign knights to replacement profile
            DB::table('knight')->where('security', $pkey)->update([
                'security' => $replacement,
                'lstmdby'  => auth()->user()->pkey,
                'lstmdts'  => now(),
            ]);
        }

        DB::table('security')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Security profile deleted.');
        return redirect('/admin/security');
    }

    /**
     * Toggle activeflg for a profile.
     */
    public function toggle($pkey)
    {
        $profile = DB::table('security')->where('pkey', $pkey)->first();
        DB::table('security')->where('pkey', $pkey)->update([
            'activeflg' => $profile->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Profile status updated.');
        return redirect("/admin/security/{$pkey}");
    }
}