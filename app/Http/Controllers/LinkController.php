<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subreddit_links = DB::select('SELECT * FROM link
                                       WHERE typcd = "subreddit" AND delflg = 0');

        $event_links = DB::select('SELECT * FROM link
                                   WHERE typcd = "event" AND delflg = 0');

        $discord_links = DB::select('SELECT * FROM link
                                     WHERE typcd = "discord" AND delflg = 0');

        $document_links = DB::select('SELECT * FROM link
                                      WHERE typcd = "document" AND delflg = 0');

        return view('link.index', ['subreddit_links' => $subreddit_links,
                                   'event_links' => $event_links,
                                   'discord_links' => $discord_links,
                                   'document_links' => $document_links,
                                  ]);
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
        //
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
