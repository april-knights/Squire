<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{
    /**
     * List all skills grouped by parent.
     */
    public function index()
    {
        // Load all skills
        $allSkills = DB::table('skill')->orderBy('skillname')->get();

        // Separate parents and children
        $parents  = $allSkills->whereNull('parentid')->sortBy('skillname');
        $children = $allSkills->whereNotNull('parentid');

        // Knight counts per skill
        $counts = DB::table('userskill')
            ->select('fkeyskill', DB::raw('COUNT(*) as cnt'))
            ->where('delflg', 0)
            ->groupBy('fkeyskill')
            ->pluck('cnt', 'fkeyskill');

        return view('admin.skills.index', compact('parents', 'children', 'counts'));
    }

    /**
     * Show a single skill.
     */
    public function show($pkey)
    {
        $skill = DB::table('skill')->where('pkey', $pkey)->first();
        if (!$skill) abort(404);

        $parent = $skill->parentid
            ? DB::table('skill')->where('pkey', $skill->parentid)->first()
            : null;

        $children = DB::table('skill')->where('parentid', $pkey)->orderBy('skillname')->get();

        $knight_count = DB::table('userskill')->where('fkeyskill', $pkey)->where('delflg', 0)->count();

        $lstmdby_name = $skill->lstmdby
            ? Knight::withoutGlobalScopes()->where('pkey', $skill->lstmdby)->value('dname')
            : null;

        return view('admin.skills.show', compact('skill', 'parent', 'children', 'knight_count', 'lstmdby_name'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $parents = DB::table('skill')->whereNull('parentid')->orderBy('skillname')->get();
        $preselectedParent = $request->get('parent');
        return view('admin.skills.create', compact('parents', 'preselectedParent'));
    }

    /**
     * Store a new skill.
     */
    public function store(Request $request)
    {
        $request->validate([
            'skillname'  => 'required|string|max:64',
            'skilldescr' => 'nullable|string|max:255',
            'parentid'   => 'nullable|integer',
            'public'     => 'nullable|boolean',
        ]);

        $now     = now();
        $adminId = auth()->user()->pkey;

        // Prevent self-reference
        $parentid = $request->input('parentid') ?: null;

        DB::table('skill')->insert([
            'skillname'  => trim($request->input('skillname')),
            'skilldescr' => $request->input('skilldescr'),
            'parentid'   => $parentid,
            'public'     => $request->has('public') ? 1 : 0,
            'crtsetdt'   => $now,
            'crtsetid'   => $adminId,
            'lstmdby'    => $adminId,
            'lstmdts'    => $now,
            'activeflg'  => 1,
            'delflg'     => 0,
        ]);

        $newPkey = DB::getPdo()->lastInsertId();

        session()->flash('success', "Skill '{$request->input('skillname')}' created.");
        return redirect("/admin/skills/{$newPkey}");
    }

    /**
     * Show edit form.
     */
    public function edit($pkey)
    {
        $skill = DB::table('skill')->where('pkey', $pkey)->first();
        if (!$skill) abort(404);

        // Parents = top-level skills excluding self and own children (prevent circular)
        $ownChildren = DB::table('skill')->where('parentid', $pkey)->pluck('pkey')->toArray();
        $excluded    = array_merge([$pkey], $ownChildren);

        $parents = DB::table('skill')
            ->whereNull('parentid')
            ->whereNotIn('pkey', $excluded)
            ->orderBy('skillname')
            ->get();

        return view('admin.skills.edit', compact('skill', 'parents'));
    }

    /**
     * Update a skill.
     */
    public function update(Request $request, $pkey)
    {
        $skill = DB::table('skill')->where('pkey', $pkey)->first();
        if (!$skill) abort(404);

        $request->validate([
            'skillname'  => 'required|string|max:64',
            'skilldescr' => 'nullable|string|max:255',
            'parentid'   => 'nullable|integer',
            'public'     => 'nullable|boolean',
        ]);

        $parentid = $request->input('parentid') ?: null;

        // Guard against self-reference
        if ($parentid == $pkey) {
            $parentid = null;
        }

        DB::table('skill')->where('pkey', $pkey)->update([
            'skillname'  => trim($request->input('skillname')),
            'skilldescr' => $request->input('skilldescr'),
            'parentid'   => $parentid,
            'public'     => $request->has('public') ? 1 : 0,
            'lstmdby'    => auth()->user()->pkey,
            'lstmdts'    => now(),
        ]);

        session()->flash('success', "Skill updated.");
        return redirect("/admin/skills/{$pkey}");
    }

    /**
     * Soft delete a skill.
     * Groups with children cannot be deleted until children are reassigned or deleted.
     */
    public function destroy(Request $request, $pkey)
    {
        $skill = DB::table('skill')->where('pkey', $pkey)->first();
        if (!$skill) abort(404);

        // Check for active children if this is a group
        $childCount = DB::table('skill')->where('parentid', $pkey)->where('delflg', 0)->count();
        if ($childCount > 0) {
            session()->flash('error', "Cannot delete — this group has {$childCount} active skill(s). Reassign or delete them first.");
            return redirect("/admin/skills/{$pkey}");
        }

        // Check for knights with this skill
        $knightCount = DB::table('userskill')->where('fkeyskill', $pkey)->where('delflg', 0)->count();
        if ($knightCount > 0) {
            session()->flash('error', "Cannot delete — {$knightCount} knight(s) have this skill assigned.");
            return redirect("/admin/skills/{$pkey}");
        }

        DB::table('skill')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Skill deleted.');
        return redirect('/admin/skills');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $skill = DB::table('skill')->where('pkey', $pkey)->first();
        if (!$skill) abort(404);

        DB::table('skill')->where('pkey', $pkey)->update([
            'activeflg' => $skill->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Skill status updated.');
        return redirect("/admin/skills/{$pkey}");
    }
}