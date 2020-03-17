<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BattalionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $batts = DB::select('SELECT * FROM battalion b
                             LEFT JOIN knight k on k.pkey = b.battlead');

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
     * @return \Illuminate\Http\Response
     */
    public function show($alias)
    {
        $batt = DB::select('SELECT * FROM battalion WHERE battalias = ?', [$alias])[0] ?? null;

        if(!$batt) {
            abort(404, 'Battalion not found.');
        }

        $battlead = DB::select('SELECT k.rname FROM battalion b
                                INNER JOIN knight k ON k.pkey = b.battlead
                                WHERE b.battalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias])[0] ?? null;

        $officers = DB::select('SELECT k.rname FROM battalion b
                                INNER JOIN knight k ON k.batt = b.pkey
                                INNER JOIN krank r on r.pkey = k.rnk
                                WHERE b.battalias = ? AND r.rval <= 5 AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias]);

        $members = DB::select('SELECT k.rname FROM battalion b
                               INNER JOIN knight k ON k.batt = b.pkey
                               WHERE b.battalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)
                               LIMIT 10', [$alias]);

        return view('battalion.show', ['batt' => $batt,
                                       'battlead' => $battlead,
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
        $batt = DB::select('SELECT * FROM battalion WHERE battalias = ?', [$alias])[0] ?? null;

        if(!$batt) {
            abort(404, 'Battalion not found.');
        }

        $battlead = DB::select('SELECT k.rname FROM battalion b
                                INNER JOIN knight k ON k.pkey = b.battlead
                                WHERE b.battalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias])[0] ?? null;

        $members = DB::select('SELECT k.rname, k.dname, r.name, r.rankdescr, e.title FROM battalion b
                               INNER JOIN knight k ON b.pkey = k.batt
                               LEFT JOIN krank r ON r.pkey = k.rnk
                               LEFT JOIN event e ON e.pkey = k.firstevent
                               WHERE b.battalias = ? AND k.pkey in(select pkey from knight where activeflg = 1 AND delflg = 0)', [$alias]);

        return view('battalion.members', ['batt' => $batt,
                                          'battlead' => $battlead,
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
