@extends('layouts.app')
@section('title', $div->name)
@section('content')
<?php /** @var \App\Model\Division $div */ ?>
<div class="row">
    <div class="col">
        <h2>Division Name</h2>
        <p>{{ $div->name }}</p>
    </div>
    <div class="col">
        <h2>Division Leader</h2>
        @if ($div->leader)
        <a href="/profile/{{ $div->leader->rname }}">
            {{ $div->leader->rname }}
        </a>
        @else
        <p>No one</p>
        @endif
    </div>
    <div class="col">
        <img class="banner" src="{{ asset('static/img/div_banners/' . $div->divalias . '.png') }}"
        alt="{{ $div->name }} Banner" title="{{ $div->name }} Banner">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="memberSearch" class="form-control" placeholder="Search by Reddit name, Discord name, rank, or first event...">
    </div>
</div>
@component('component.membertable', [
    'members'   => $members,
    'sort'      => $sort,
    'direction' => $direction,
    'alias'     => 'div/' . $div->divalias,
])
@endcomponent
@endsection