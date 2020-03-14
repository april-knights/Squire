<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $knight_orders = DB::select('SELECT * from orders o
                                     LEFT JOIN knight k ON k.pkey = o.authorid
                                     WHERE o.level > 8');

        $officer_orders = DB::select('SELECT * from orders o
                                      LEFT JOIN knight k ON k.pkey = o.authorid
                                      WHERE o.level <= 8 AND o.level > 7');

        $commander_orders = DB::select('SELECT * from orders o
                                        LEFT JOIN knight k ON k.pkey = o.authorid
                                        WHERE o.level <= 11');

        return view('orders.index', ['knight_orders' => $knight_orders,
                                     'officer_orders' => $officer_orders,
                                     'commander_orders' => $commander_orders,
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
