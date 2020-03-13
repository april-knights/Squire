<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $divs = DB::select('SELECT * FROM division d LEFT JOIN knight k on k.pkey = d.divlead');

        return view('division.index', ['divs' => $divs]);
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
     * @param  int  $alias
     * @return \Illuminate\Http\Response
     */
    public function show($alias)
    {
        $div = DB::select('SELECT * FROM division WHERE divalias = ?', [$alias])[0];
        $divlead = DB::select('SELECT k.rname FROM division d INNER JOIN knight k ON k.pkey = d.divlead WHERE d.divalias = ?', [$alias])[0] ?? null;
        $members = DB::select('SELECT k.rname FROM division d INNER JOIN knight k ON k.batt = d.pkey WHERE d.divalias = ?', [$alias]);
        $officers = DB::select('SELECT k.rname FROM division d INNER JOIN knight k ON k.batt = d.pkey INNER JOIN krank r on r.pkey = k.rnk
                                WHERE d.divalias = ? AND r.rval <= 5', [$alias]);

        return view('division.show', ['div' => $div,
                                       'divlead' => $divlead,
                                       'members' => $members,
                                       'officers' => $officers,
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
