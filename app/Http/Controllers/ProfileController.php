<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

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
        $rank = DB::select('SELECT name, rankdescr FROM krank WHERE pkey = ?', [$knight->rnk])[0] ?? null;
        $batt = DB::select('SELECT name, battalias FROM battalion WHERE pkey = ?', [$knight->batt])[0] ?? null;
        $skills = DB::select('SELECT s.skillname FROM skill s INNER JOIN userskill u ON s.pkey = u.fkeyskill
                              WHERE u.fkeyuser = ? AND s.delflg = 0', [$knight->pkey]);
        // TODO: Show skill parents as well
        // Certain fields are limited to councillors and the user themselves
        $show_sensitive = (Auth::user()->isCouncillor()) || (Auth::id() == $knight->pkey);

        // Other fields are limited to officers from this knight's battalion
        $show_irl = (Auth::user()->isOfficer($knight->batt)) || (Auth::id() == $knight->pkey);

        return view('profile.show', ['knight' => $knight,
                                     'rank' => $rank,
                                     'batt' => $batt,
                                     'skills' => $skills,
                                     'show_sensitive' => $show_sensitive,
                                     'show_irl' => $show_irl,
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
