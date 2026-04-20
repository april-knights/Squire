<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankController extends Controller
{
    /**
     * List all ranks ordered by rval.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'rval');
        $direction = $request->get('direction', 'asc');

        $allowed_sorts = ['pkey', 'name', 'rval', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'rval';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $ranks = DB::table('krank')
            ->orderBy($sort, $direction)
            ->orderBy('name', 'asc')
            ->get();

        // Knight count per rank
        $counts = DB::table('knight')
            ->select('rnk', DB::raw('COUNT(*) as cnt'))
            ->where('delflg', 0)
            ->groupBy('rnk')
            ->pluck('cnt', 'rnk');

        return view('admin.ranks.index', compact('ranks', 'counts', 'sort', 'direction'));
    }

    /**
     * Show a single rank.
     */
    public function show($pkey)
    {
        $rank = DB::table('krank')->where('pkey', $pkey)->first();
        if (!$rank) abort(404);

        $knight_count = DB::table('knight')->where('rnk', $pkey)->where('delflg', 0)->count();

        $lstmdby_name = null;
        if ($rank->lstmdby) {
            $lstmdby_name = Knight::withoutGlobalScopes()->where('pkey', $rank->lstmdby)->value('dname');
        }
        $crtsetid_name = null;
        if ($rank->crtsetid) {
            $crtsetid_name = Knight::withoutGlobalScopes()->where('pkey', $rank->crtsetid)->value('dname');
        }

        return view('admin.ranks.show', compact('rank', 'knight_count', 'lstmdby_name', 'crtsetid_name'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('admin.ranks.create');
    }

    /**
     * Store a new rank.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:20',
            'rval'     => 'required|integer|min:1|max:99',
            'rankdescr' => 'nullable|string|max:255',
            'uniqe'    => 'nullable|boolean',
        ]);

        $nextPkey = DB::table('krank')->max('pkey') + 1;
        $now      = now();
        $adminId  = auth()->user()->pkey;

        DB::table('krank')->insert([
            'pkey'      => $nextPkey,
            'name'      => $request->input('name'),
            'rval'      => $request->input('rval'),
            'rankdescr' => $request->input('rankdescr'),
            'uniqe'     => $request->has('uniqe') ? 1 : 0,
            'crtsetdt'  => $now,
            'crtsetid'  => $adminId,
            'lstmdby'   => $adminId,
            'lstmdts'   => $now,
            'activeflg' => 1,
            'delflg'    => 0,
        ]);

        session()->flash('success', "Rank '{$request->input('name')}' created.");
        return redirect("/admin/ranks/{$nextPkey}");
    }

    /**
     * Show the edit form.
     */
    public function edit($pkey)
    {
        $rank = DB::table('krank')->where('pkey', $pkey)->first();
        if (!$rank) abort(404);

        return view('admin.ranks.edit', compact('rank'));
    }

    /**
     * Update a rank.
     */
    public function update(Request $request, $pkey)
    {
        $rank = DB::table('krank')->where('pkey', $pkey)->first();
        if (!$rank) abort(404);

        $request->validate([
            'name'      => 'required|string|max:20',
            'rval'      => 'required|integer|min:1|max:99',
            'rankdescr' => 'nullable|string|max:255',
            'uniqe'     => 'nullable|boolean',
        ]);

        DB::table('krank')->where('pkey', $pkey)->update([
            'name'      => $request->input('name'),
            'rval'      => $request->input('rval'),
            'rankdescr' => $request->input('rankdescr'),
            'uniqe'     => $request->has('uniqe') ? 1 : 0,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', "Rank updated.");
        return redirect("/admin/ranks/{$pkey}");
    }

    /**
     * Soft delete a rank.
     * If knights are assigned, require reassignment first.
     */
    public function destroy(Request $request, $pkey)
    {
        $rank = DB::table('krank')->where('pkey', $pkey)->first();
        if (!$rank) abort(404);

        $knight_count = DB::table('knight')->where('rnk', $pkey)->where('delflg', 0)->count();

        if ($knight_count > 0) {
            $replacement = $request->input('replacement_pkey');
            if (!$replacement) {
                session()->flash('error', 'This rank has knights assigned. Select a replacement rank before deleting.');
                return redirect("/admin/ranks/{$pkey}");
            }

            DB::table('knight')->where('rnk', $pkey)->update([
                'rnk'     => $replacement,
                'lstmdby' => auth()->user()->pkey,
                'lstmdts' => now(),
            ]);
        }

        DB::table('krank')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Rank deleted.');
        return redirect('/admin/ranks');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $rank = DB::table('krank')->where('pkey', $pkey)->first();
        if (!$rank) abort(404);

        DB::table('krank')->where('pkey', $pkey)->update([
            'activeflg' => $rank->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Rank status updated.');
        return redirect("/admin/ranks/{$pkey}");
    }
}