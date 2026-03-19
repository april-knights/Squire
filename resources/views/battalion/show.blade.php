@extends('layouts.app')
 
@section('title', $batt->name)

@section('content')
@component('component.battoverview', ['batt' => $batt, 'battlead' => $battlead])
@endcomponent

@if($can_edit)
<div class="row">
    <div class="col">
        <a href="/battalion/{{ $batt->battalias }}/edit"><i class="fas fa-edit"></i> Edit Battalion</a>
    </div>
</div>
@endif

@if($can_add_knight)
<div class="row">
    <div class="col">
        <a href="/profile/new?batt={{ $batt->pkey }}"><i class="fas fa-plus"></i> New Knight</a>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h2>Battalion Motto</h2>
                <p>{{ $batt->motto }}</p>
            </div>
            <div class="col-md-6">
                <h2>Battalion Officers</h2>
                <ul class="officers">
                @forelse ($officers as $officer)
                    <li>
                        <a href="/profile/{{ $officer->rname }}">
                            {{ $officer->rname }}
                        </a>
                    </li>
                @empty
                    <li>No one</li>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h2>Battalion Members</h2>
        <ul class="members">
        @forelse ($members as $member)
            <li>
                <a href="/profile/{{ $member->rname }}">
                    {{ $member->rname }}
                </a>
            </li>
        @empty
            <li>No one</li>
        @endforelse
        </ul>
        <a class="font-italic" href="/battalion/{{ $batt->battalias }}/members">See all…</a>
    </div>
</div>
<div class="row">
    <div class="col">
        <h2>Battalion Description</h2>
        <p>{{ $batt->battdescr }}</p>
    </div>
</div>
@endsection
