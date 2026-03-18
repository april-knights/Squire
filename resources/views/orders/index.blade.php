@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<?php /** @var \App\Model\Knight $user */ ?>

@if($battalion_orders->isNotEmpty())
<h2>Battalion Orders</h2>
@component('component.orderlist', [
    'orders'         => $battalion_orders,
    'can_edit_order' => $can_create && Auth::user()->isOfficer(Auth::user()->batt),
    'can_delete'     => $can_delete,
])
@endcomponent
@endif

<h2>Knights</h2>
@component('component.orderlist', [
    'orders'         => $knight_orders,
    'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
    'can_delete'     => $can_delete,
])
@endcomponent

@if($user_rank <= \App\Model\Rank::HIGHEST_OFFICER_RANK)
<h2>Officers</h2>
@component('component.orderlist', [
    'orders'         => $officer_orders,
    'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
    'can_delete'     => $can_delete,
])
@endcomponent
@endif

@if($user_rank <= \App\Model\Rank::HIGHEST_COMMANDER_RANK)
<h2>Commanders</h2>
@component('component.orderlist', [
    'orders'         => $commander_orders,
    'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
    'can_delete'     => $can_delete,
])
@endcomponent
@endif

@if($can_create)
<div class="row mt-3">
    <div class="col">
        <a href="/orders/create" class="btn btn-success">New Order</a>
    </div>
</div>
@endif

@endsection
