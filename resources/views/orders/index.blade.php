@extends('layouts.app')

@section('title', 'Battalions')

@section('content')
    <h2 class='ordertitle'>Knights</h2>
    @component('component.orderlist', ['orders' => $knight_orders])
    @endcomponent

    @if(Auth::user()->getRankVal() <= \App\Models\Rank::HIGHEST_OFFICER_RANK)
        <h2>Officers</h2>
        @component('component.orderlist', ['orders' => $officer_orders])
        @endcomponent
    @endif

    @if(Auth::user()->getRankVal() <= \App\Models\Rank::HIGHEST_COMMANDER_RANK)
        <h2>Commanders</h2>
        @component('component.orderlist', ['orders' => $commander_orders])
        @endcomponent
    @endif
@endsection
