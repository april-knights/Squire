<?php

namespace App\Http\Controllers;

use App\Model\Battalion;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        if(!$batt) {
            abort(404, 'Battalion not found.');
        }

        return view('battalion.show', ['batt' => $batt,
                                       'battlead' => $batt->leader,
                                       'members' => $batt->members()->limit(10)->get(),
                                       'officers' => $batt->officers()->get()->sortBy(fn(Knight $o) => $o->rank->rval),
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

        if(!$batt) {
            abort(404, 'Battalion not found.');
        }

        return view('battalion.members', ['batt' => $batt]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function edit($rname)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
