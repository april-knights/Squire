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
        $divs = DB::select('SELECT * FROM division d LEFT JOIN knight k on k.pkey = d.divlead WHERE d.delflg = 0');

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
        $div = DB::select('SELECT * FROM division WHERE divalias = ?', [$alias])[0] ?? null;

        if(!$div) {
            abort(404, 'Division not found.');
        }

        $divlead = DB::select('SELECT k.rname FROM division d
                               INNER JOIN knight k ON k.pkey = d.divlead
                               WHERE d.divalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias])[0] ?? null;

        $officers = DB::select('SELECT k.rname FROM knight k
                                INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                                INNER JOIN division d ON d.pkey = dk.fkeydivision
                                INNER JOIN krank r ON r.pkey = k.rnk
                                WHERE d.divalias = ? AND r.rval <= 5 AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias]);

        $members = DB::select('SELECT k.rname FROM knight k
                               INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                               INNER JOIN division d ON d.pkey = dk.fkeydivision
                               WHERE d.divalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)
                               LIMIT 10', [$alias]);



        return view('division.show', ['div' => $div,
                                       'divlead' => $divlead,
                                       'members' => $members,
                                       'officers' => $officers,
                                     ]);
    }

    /**
     * Display the complete member list.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function members($alias)
    {
        $div = DB::select('SELECT * FROM division WHERE divalias = ?', [$alias])[0] ?? null;

        if(!$div) {
            abort(404, 'Division not found.');
        }

        $divlead = DB::select('SELECT k.rname FROM division d
                               INNER JOIN knight k ON k.pkey = d.divlead
                               WHERE d.divalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias])[0] ?? null;

        $members = DB::select('SELECT k.rname, k.dname, r.name, r.rankdescr, e.title FROM knight k
                               INNER JOIN divknight dk ON dk.fkeyknight = k.pkey
                               INNER JOIN division d ON d.pkey = dk.fkeydivision
                               LEFT JOIN krank r ON r.pkey = k.rnk
                               LEFT JOIN event e ON e.pkey = k.firstevent
                               WHERE d.divalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias]);

        return view('division.members', ['div' => $div,
                                          'divlead' => $divlead,
                                          'members' => $members,
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
