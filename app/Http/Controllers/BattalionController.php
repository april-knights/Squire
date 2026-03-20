<?php

namespace App\Http\Controllers;

use App\Model\Battalion;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\View\View;

use DB;
use Log;
use Auth;

class BattalionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $batts = Battalion::query()->with('leader')->get();

        return view('battalion.index', ['batts' => $batts]);
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
     * @param  string  $alias
     * @return View
     */
    public function show($alias)
    {
        $batt = Battalion::where('battalias', $alias)->first();

        if (!$batt) {
            abort(404, 'Battalion not found.');
        }

        return view('battalion.show', [
            'batt'     => $batt,
            'battlead' => $batt->leader,
            'members'  => $batt->members()->limit(10)->get(),
            'officers' => $batt->officers->sortBy(fn(Knight $o) => $o->rank->rval),
            'can_edit' => Auth::user()->checkSecurity(Battalion::getPermission(Battalion::PERMISSION_MODIFY)),
            'can_add_knight' => Auth::user()->isCouncillor() || Auth::user()->isOfficer($batt->pkey),
        ]);
    }

    /**
     * Display the complete member list.
     *
     * @param  string  $alias
     * @return View
     */
    public function members($alias)
    {
        $batt = Battalion::where('battalias', $alias)->first();

        if (!$batt) {
            abort(404, 'Battalion not found.');
        }

        return view('battalion.members', ['batt' => $batt]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $alias
     * @return View
     */
    public function edit($alias)
    {
        $batt = Battalion::where('battalias', $alias)->first();

        abort_if(!$batt, 404, 'Battalion not found.');
        abort_if(
            !Auth::user()->checkSecurity(Battalion::getPermission(Battalion::PERMISSION_MODIFY)),
            401,
            'Not authorized to edit battalion.'
        );

        return view('battalion.edit', [
            'batt'        => $batt,
            'all_knights' => Knight::where('batt', $batt->pkey)->orderBy('rname')->get(),
            'can_delete'  => Auth::user()->checkSecurity(Battalion::getPermission(Battalion::PERMISSION_DELETE)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $alias
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $alias)
    {
        $batt = Battalion::where('battalias', $alias)->first();

        abort_if(!$batt, 404, 'Battalion not found.');

        if (!Auth::user()->checkSecurity(Battalion::getPermission(Battalion::PERMISSION_MODIFY))) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to edit battalion ' . $alias . '!');
            abort(401, 'Not authorized to edit battalion.');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:30',
            'battalias' => 'required|alpha_dash|max:10|unique:battalion,battalias,' . $batt->pkey . ',pkey',
            'color'     => 'nullable|string|max:15',
            'battlead'  => 'nullable|integer|exists:knight,pkey',
            'battsec1'  => 'nullable|integer|exists:knight,pkey',
            'battsec2'  => 'nullable|integer|exists:knight,pkey',
            'motto'     => 'nullable|string|max:64',
            'battdescr' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use (&$validated, &$batt) {
            $batt->fill([
                'name'      => $validated['name'],
                'battalias' => $validated['battalias'],
                'color'     => $validated['color'] ?? $batt->color,
                'battlead'  => $validated['battlead'] ?? null,
                'battsec1'  => $validated['battsec1'] ?? null,
                'battsec2'  => $validated['battsec2'] ?? null,
                'motto'     => $validated['motto'] ?? $batt->motto,
                'battdescr' => $validated['battdescr'] ?? $batt->battdescr,
            ])->save();
        });

        return redirect('/battalion/' . $validated['battalias']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $alias
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $alias)
    {
        if (!Auth::user()->checkSecurity(Battalion::getPermission(Battalion::PERMISSION_DELETE))) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to delete battalion ' . $alias . '!');
            abort(401, 'You are not authorized to delete that battalion!');
        }

        $batt = Battalion::where('battalias', $alias)->first();

        if (!$batt) {
            abort(404, 'Battalion does not exist!');
        }

        $batt->delflg = true;
        $batt->save();

        $request->session()->flash('success', 'Deleted battalion ' . $batt->name . '.');

        return redirect('/battalion');
    }
}
