@extends('layouts.app')

@section('title', 'Battalions')

@section('content')
<h2 class='ordertitle'>Knights</h2>
@component('component.orderlist', ['orders' => $knight_orders])
@endcomponent

@if(Auth::user()->getRankVal() <= 8)
    <h2>Officers</h2>
    @component('component.orderlist', ['orders' => $officer_orders])
    @endcomponent
@endif

@if(Auth::user()->getRankVal() <= 5)
    <h2>Commanders</h2>
    @component('component.orderlist', ['orders' => $commander_orders])
    @endcomponent
@endif
@endsection
