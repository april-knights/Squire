<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rank;
use Illuminate\Http\Request;

use Auth;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user_rank = Auth::user()->getRankVal();
        $knight_orders = Order::where('level', '>', Rank::HIGHEST_OFFICER_RANK)
            ->where('level', '>=', $user_rank);
        $officer_orders = Order::whereBetween('level', [Rank::HIGHEST_COMMANDER_RANK + 1, Rank::HIGHEST_OFFICER_RANK])
            ->where('level', '>=', $user_rank);
        $commander_orders = Order::where('level', '<=', Rank::HIGHEST_COMMANDER_RANK)
            ->where('level', '>=', $user_rank);

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
