<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{
    /**
     * List all divisions.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        $allowed_sorts = ['pkey', 'name', 'divalias', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'name';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $divisions = DB::table('division')->orderBy($sort, $direction)->get();

        // Member counts per division (active divknight records only)
        $counts = DB::table('divknight')
            ->select('fkeydivision', DB::raw('COUNT(*) as cnt'))
            ->where('delflg', 0)
            ->groupBy('fkeydivision')
            ->pluck('cnt', 'fkeydivision');

        // Resolve leader names
        $leaderNames = [];
        foreach ($divisions as $div) {
            if ($div->divlead) {
                $leaderNames[$div->pkey] = Knight::withoutGlobalScopes()
                    ->where('pkey', $div->divlead)
                    ->value('rname');
            }
        }

        return view('admin.divisions.index', compact('divisions', 'counts', 'leaderNames', 'sort', 'direction'));
    }

    /**
     * Show a single division with members.
     */
    public function show($pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        $members = DB::table('divknight')
            ->join('knight', 'knight.pkey', '=', 'divknight.fkeyknight')
            ->join('krank', 'krank.pkey', '=', 'knight.rnk')
            ->where('divknight.fkeydivision', $pkey)
            ->where('divknight.delflg', 0)
            ->select('knight.pkey', 'knight.rname', 'knight.dname', 'krank.name as rankname', 'krank.rval', 'divknight.pkey as pivot_pkey')
            ->orderBy('krank.rval')
            ->orderBy('knight.rname')
            ->get();

        $leaderName = $division->divlead
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divlead)->first(['pkey', 'rname', 'dname'])
            : null;
        $sec1Name = $division->divsec1
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divsec1)->first(['pkey', 'rname', 'dname'])
            : null;
        $sec2Name = $division->divsec2
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divsec2)->first(['pkey', 'rname', 'dname'])
            : null;

        $lstmdby_name = $division->lstmdby
            ? Knight::withoutGlobalScopes()->where('pkey', $division->lstmdby)->value('dname')
            : null;

        return view('admin.divisions.show', compact(
            'division', 'members', 'leaderName', 'sec1Name', 'sec2Name', 'lstmdby_name'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * Store a new division.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:30|unique:division,name',
            'divalias' => 'required|string|max:10|unique:division,divalias',
            'divdescr' => 'nullable|string|max:500',
            'motto'    => 'nullable|string|max:64',
            'color'    => 'nullable|string|max:15',
            'divlead'  => 'nullable|integer',
            'divsec1'  => 'nullable|integer',
            'divsec2'  => 'nullable|integer',
        ]);

        $nextPkey = DB::table('division')->max('pkey') + 1;
        $now      = now();
        $adminId  = auth()->user()->pkey;

        DB::table('division')->insert([
            'pkey'      => $nextPkey,
            'name'      => $request->input('name'),
            'divalias'  => strtolower($request->input('divalias')),
            'divdescr'  => $request->input('divdescr'),
            'motto'     => $request->input('motto'),
            'color'     => $request->input('color'),
            'divlead'   => $request->input('divlead') ?: null,
            'divsec1'   => $request->input('divsec1') ?: null,
            'divsec2'   => $request->input('divsec2') ?: null,
            'crtsetdt'  => $now,
            'crtsetid'  => $adminId,
            'lstmdby'   => $adminId,
            'lstmdts'   => $now,
            'activeflg' => 1,
            'delflg'    => 0,
        ]);

        session()->flash('success', "Division '{$request->input('name')}' created.");
        return redirect("/admin/divisions/{$nextPkey}");
    }

    /**
     * Show the edit form.
     */
    public function edit($pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        $leaderName = $division->divlead
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divlead)->first(['pkey', 'rname', 'dname'])
            : null;
        $sec1Name = $division->divsec1
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divsec1)->first(['pkey', 'rname', 'dname'])
            : null;
        $sec2Name = $division->divsec2
            ? Knight::withoutGlobalScopes()->where('pkey', $division->divsec2)->first(['pkey', 'rname', 'dname'])
            : null;

        return view('admin.divisions.edit', compact('division', 'leaderName', 'sec1Name', 'sec2Name'));
    }

    /**
     * Update a division.
     */
    public function update(Request $request, $pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        $request->validate([
            'name'     => "required|string|max:30|unique:division,name,{$pkey},pkey",
            'divdescr' => 'nullable|string|max:500',
            'motto'    => 'nullable|string|max:64',
            'color'    => 'nullable|string|max:15',
            'divlead'  => 'nullable|integer',
            'divsec1'  => 'nullable|integer',
            'divsec2'  => 'nullable|integer',
        ]);

        DB::table('division')->where('pkey', $pkey)->update([
            'name'     => $request->input('name'),
            'divdescr' => $request->input('divdescr'),
            'motto'    => $request->input('motto'),
            'color'    => $request->input('color'),
            'divlead'  => $request->input('divlead') ?: null,
            'divsec1'  => $request->input('divsec1') ?: null,
            'divsec2'  => $request->input('divsec2') ?: null,
            'lstmdby'  => auth()->user()->pkey,
            'lstmdts'  => now(),
        ]);

        session()->flash('success', "Division updated.");
        return redirect("/admin/divisions/{$pkey}");
    }

    /**
     * Soft delete a division.
     */
    public function destroy($pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        DB::table('division')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Division deleted.');
        return redirect('/admin/divisions');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        DB::table('division')->where('pkey', $pkey)->update([
            'activeflg' => $division->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Division status updated.');
        return redirect("/admin/divisions/{$pkey}");
    }

    /**
     * Add a knight to a division.
     * POST /admin/divisions/{pkey}/members
     */
    public function addMember(Request $request, $pkey)
    {
        $division = DB::table('division')->where('pkey', $pkey)->first();
        if (!$division) abort(404);

        $request->validate([
            'knight_pkey' => 'required|integer',
        ]);

        $knightPkey = $request->input('knight_pkey');

        // Check knight exists
        $knight = Knight::withoutGlobalScopes()->where('pkey', $knightPkey)->first();
        if (!$knight) {
            session()->flash('error', 'Knight not found.');
            return redirect("/admin/divisions/{$pkey}");
        }

        // Check for existing active membership
        $existing = DB::table('divknight')
            ->where('fkeydivision', $pkey)
            ->where('fkeyknight', $knightPkey)
            ->where('delflg', 0)
            ->first();

        if ($existing) {
            session()->flash('error', "{$knight->rname} is already a member of this division.");
            return redirect("/admin/divisions/{$pkey}");
        }

        // Check for soft-deleted record to restore instead of inserting
        $deleted = DB::table('divknight')
            ->where('fkeydivision', $pkey)
            ->where('fkeyknight', $knightPkey)
            ->where('delflg', 1)
            ->first();

        $now     = now();
        $adminId = auth()->user()->pkey;

        if ($deleted) {
            DB::table('divknight')->where('pkey', $deleted->pkey)->update([
                'delflg'  => 0,
                'lstmdby' => $adminId,
                'lstmdts' => $now,
            ]);
        } else {
            DB::table('divknight')->insert([
                'fkeydivision' => $pkey,
                'fkeyknight'   => $knightPkey,
                'crtsetdt'     => $now,
                'crtsetid'     => $adminId,
                'lstmdby'      => $adminId,
                'lstmdts'      => $now,
                'delflg'       => 0,
            ]);
        }

        session()->flash('success', "{$knight->rname} added to division.");
        return redirect("/admin/divisions/{$pkey}");
    }

    /**
     * Remove a knight from a division (soft delete pivot record).
     * POST /admin/divisions/{pkey}/members/{pivotPkey}/remove
     */
    public function removeMember($pkey, $pivotPkey)
    {
        DB::table('divknight')->where('pkey', $pivotPkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Member removed from division.');
        return redirect("/admin/divisions/{$pkey}");
    }

    /**
     * Knight search endpoint for leadership/member lookups.
     * GET /admin/knights/search?q=foo&exclude_division=1
     */
    public function knightSearch(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $excludeDivision = $request->get('exclude_division');

        $query = Knight::withoutGlobalScopes()
            ->where('delflg', 0)
            ->where(function ($q2) use ($q) {
                $q2->where('rname', 'LIKE', "%{$q}%")
                   ->orWhere('dname', 'LIKE', "%{$q}%");
            })
            ->select('pkey', 'rname', 'dname');

        // Optionally exclude knights already in this division
        if ($excludeDivision) {
            $alreadyIn = DB::table('divknight')
                ->where('fkeydivision', $excludeDivision)
                ->where('delflg', 0)
                ->pluck('fkeyknight');

            $query->whereNotIn('pkey', $alreadyIn);
        }

        return response()->json($query->orderBy('rname')->limit(10)->get());
    }
}