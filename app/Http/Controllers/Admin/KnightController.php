<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Battalion;
use App\Model\Knight;
use App\Model\Rank;
use App\Model\Security;
use Illuminate\Http\Request;

class KnightController extends Controller
{
    /**
     * Display all knights (including inactive and deleted) with sort and search.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'rname');
        $direction = $request->get('direction', 'asc');

        $allowed_sorts = ['rname', 'dname', 'email', 'discordid', 'batt', 'rnk', 'security', 'last_login', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) {
            $sort = 'rname';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        // withoutGlobalScopes() on each eager load bypasses HasActiveTrait filters
        $knights = Knight::withoutGlobalScopes()
            ->with([
                'rank'      => fn($q) => $q->withoutGlobalScopes(),
                'battalion' => fn($q) => $q->withoutGlobalScopes(),
                'skills'    => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->leftJoin('security', 'security.pkey', '=', 'knight.security')
            ->select('knight.*', 'security.secname as secname')
            ->orderBy($sort === 'security' ? 'security.secname' : "knight.{$sort}", $direction)
            ->get();

        $battalions = Battalion::orderBy('name')->get();
        $ranks      = Rank::orderBy('rval')->get();
        $securities = Security::orderBy('pkey')->get();

        return view('admin.knights.index', compact('knights', 'sort', 'direction', 'battalions', 'ranks', 'securities'));
    }

    /**
     * Show a read-only admin summary for a single knight.
     */
    public function show($pkey)
    {
        $knight = Knight::withoutGlobalScopes()
            ->with([
                'rank'      => fn($q) => $q->withoutGlobalScopes(),
                'battalion' => fn($q) => $q->withoutGlobalScopes(),
                'security'  => fn($q) => $q->withoutGlobalScopes(),
                'divisions' => fn($q) => $q->withoutGlobalScopes(),
                'skills'    => fn($q) => $q->withoutGlobalScopes(),
                'badges'    => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->findOrFail($pkey);

        return view('admin.knights.show', compact('knight'));
    }

    /**
     * Show the edit form for a knight.
     */
    public function edit($pkey)
    {
        $knight     = Knight::withoutGlobalScopes()->findOrFail($pkey);
        $battalions = Battalion::withoutGlobalScopes()->orderBy('name')->get();
        $ranks      = Rank::withoutGlobalScopes()->orderBy('rval')->get();
        $securities = Security::withoutGlobalScopes()->orderBy('pkey')->get();

        return view('admin.knights.edit', compact('knight', 'battalions', 'ranks', 'securities'));
    }

    /**
     * Update a knight's admin-editable fields.
     */
    public function update(Request $request, $pkey)
    {
        $knight = Knight::withoutGlobalScopes()->findOrFail($pkey);

        $validated = $request->validate([
            'rname'     => 'required|string|max:255',
            'dname'     => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'discordid' => 'nullable|string|max:64',
            'batt'      => 'nullable|integer',
            'rnk'       => 'nullable|integer',
            'security'  => 'nullable|integer',
            'inttrans'  => 'nullable|string|max:255',
            'onote'     => 'nullable|string',
        ]);

        $validated['lstmdby'] = auth()->user()->rname;
        $validated['lstmdts'] = now();

        $knight->fill($validated)->save();

        session()->flash('success', "Knight {$knight->rname} updated successfully.");
        return redirect("/admin/knights/{$pkey}");
    }

    /**
     * Toggle activeflg or delflg for a knight.
     * POST /admin/knights/{pkey}/toggle
     * Expects: field = 'activeflg' or 'delflg'
     */
    public function toggle(Request $request, $pkey)
    {
        $knight = Knight::withoutGlobalScopes()->findOrFail($pkey);

        $field = $request->input('field');
        if (!in_array($field, ['activeflg', 'delflg'])) {
            abort(422, 'Invalid toggle field.');
        }

        $knight->{$field}  = $knight->{$field} ? 0 : 1;
        $knight->lstmdby   = auth()->user()->rname;
        $knight->lstmdts   = now();
        $knight->save();

        $label = $field === 'activeflg' ? 'Active status' : 'Deleted status';
        session()->flash('success', "{$label} for {$knight->rname} updated.");
        return redirect("/admin/knights/{$pkey}");
    }
}