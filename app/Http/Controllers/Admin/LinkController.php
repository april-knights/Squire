<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use App\Model\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkController extends Controller
{
    private const TYPES = [
        Link::TYPE_SUBREDDIT,
        Link::TYPE_EVENT,
        Link::TYPE_DISCORD,
        Link::TYPE_DOCUMENT,
    ];

    /**
     * List all links grouped by type.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'typcd');
        $direction = $request->get('direction', 'asc');

        $allowed_sorts = ['pkey', 'typcd', 'linknm', 'orderid', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'typcd';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $links = DB::table('link')
            ->orderBy($sort, $direction)
            ->orderBy('orderid')
            ->orderBy('linknm')
            ->get();

        $types = self::TYPES;

        return view('admin.links.index', compact('links', 'sort', 'direction', 'types'));
    }

    /**
     * Show a single link.
     */
    public function show($pkey)
    {
        $link = DB::table('link')->where('pkey', $pkey)->first();
        if (!$link) abort(404);

        $lstmdby_name = $link->lstmdby
            ? Knight::withoutGlobalScopes()->where('pkey', $link->lstmdby)->value('dname')
            : null;

        return view('admin.links.show', compact('link', 'lstmdby_name'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $types = self::TYPES;
        return view('admin.links.create', compact('types'));
    }

    /**
     * Store a new link.
     */
    public function store(Request $request)
    {
        $request->validate([
            'typcd'    => 'required|in:' . implode(',', self::TYPES),
            'linknm'   => 'required|string|max:50',
            'linkdesc' => 'required|string|max:255',
            'linkurl'  => 'nullable|url|max:150',
            'imgurl'   => 'nullable|string|max:150',
        ]);

        $now     = now();
        $adminId = auth()->user()->pkey;

        DB::table('link')->insert([
            'typcd'    => $request->input('typcd'),
            'linknm'   => $request->input('linknm'),
            'linkdesc' => $request->input('linkdesc'),
            'orderid'  => $request->input('orderid', 0),
            'linkurl'  => trim($request->input('linkurl')),
            'imgurl'   => $request->input('imgurl'),
            'crtsetdt' => $now,
            'crtsetid' => $adminId,
            'lstmdby'  => $adminId,
            'lstmdts'  => $now,
            'activeflg'=> 1,
            'delflg'   => 0,
        ]);

        $newPkey = DB::getPdo()->lastInsertId();
        session()->flash('success', "Link '{$request->input('linknm')}' created.");
        return redirect("/admin/links/{$newPkey}");
    }

    /**
     * Show edit form.
     */
    public function edit($pkey)
    {
        $link = DB::table('link')->where('pkey', $pkey)->first();
        if (!$link) abort(404);

        $types = self::TYPES;
        return view('admin.links.edit', compact('link', 'types'));
    }

    /**
     * Update a link.
     */
    public function update(Request $request, $pkey)
    {
        $link = DB::table('link')->where('pkey', $pkey)->first();
        if (!$link) abort(404);

        $request->validate([
            'typcd'    => 'required|in:' . implode(',', self::TYPES),
            'linknm'   => 'required|string|max:50',
            'linkdesc' => 'required|string|max:255',
            'linkurl'  => 'nullable|url|max:150',
            'imgurl'   => 'nullable|string|max:150',
        ]);

        DB::table('link')->where('pkey', $pkey)->update([
            'typcd'    => $request->input('typcd'),
            'linknm'   => $request->input('linknm'),
            'linkdesc' => $request->input('linkdesc'),
            'orderid'  => $request->input('orderid', 0),
            'linkurl'  => trim($request->input('linkurl')),
            'imgurl'   => $request->input('imgurl'),
            'lstmdby'  => auth()->user()->pkey,
            'lstmdts'  => now(),
        ]);

        session()->flash('success', "Link updated.");
        return redirect("/admin/links/{$pkey}");
    }

    /**
     * Soft delete.
     */
    public function destroy($pkey)
    {
        $link = DB::table('link')->where('pkey', $pkey)->first();
        if (!$link) abort(404);

        DB::table('link')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Link deleted.');
        return redirect('/admin/links');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $link = DB::table('link')->where('pkey', $pkey)->first();
        if (!$link) abort(404);

        DB::table('link')->where('pkey', $pkey)->update([
            'activeflg' => $link->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Link status updated.');
        return redirect("/admin/links/{$pkey}");
    }
}