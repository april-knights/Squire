<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $knights = DB::select('select * from knights');

        return view('knight.index', ['knights' => $knights]);
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
     * @param  int  $rname
     * @return \Illuminate\Http\Response
     */
    public function show($rname)
    {
        $knight = DB::select('SELECT * FROM knight WHERE rname = ?', [$rname])[0];
        $rank = DB::select('SELECT name FROM krank WHERE pkey = ?', [$knight->rnk])[0]->name;
        $batt = DB::select('SELECT name FROM battalion WHERE pkey = ?', [$knight->batt])[0]->name;
        $skills = DB::select('SELECT * FROM skill INNER JOIN userskill ON skill.pkey = userskill.fkeyskill WHERE userskill.fkeyuser = ? AND skill.delflg = 0',
            [$knight->pkey]);
        // TODO: Show skill parents as well

        return view('profile.show', ['knight' => $knight, 'rank' => $rank, 'batt' => $batt, 'skills' => $skills]);
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
