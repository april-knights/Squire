@extends('layouts.app')

@if ($rank)
    @section('title', $rank->name . ' ' . $knight->rname)
@else
    @section('title', $knight->rname)
@endif

@section('content')
<div class="row">
    <div class="col-md-4">
        <h2>Reddit Name</h2>
        <p>/u/{{ $knight->rname }}</p>
    </div>
    <div class="col-md-4">
        <h2>Discord Name</h2>
        <p>{{ $knight->dname }}</p>
    </div>
    <div class="col-md-3">
        @if ($show_sensitive)
        <h2>
            Knight ID
            <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="Uniquely identifies you. Only visible to councillors and you."></i>
        </h2>
        <p>{{ $knight->knum }}</p>
        @endif
    </div>
    @if($can_edit)
    <div class="col-md-1">
        <a href="/profile/{{ $knight->rname }}/edit"><i class="fas fa-edit"></i></a>
    </div>
    @endif
</div>
<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h2>Battalion</h2>
                @if($batt)
                <a href="/battalion/{{ $batt->battalias }}">{{ $batt->name }}</a>
                @else
                <p>None</p>
                @endif
            </div>
            <div class="col-md-6">
                <h2>Rank</h2>
                @if($rank)
                <p>
                    {{ $rank->name }}
                    <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{ $rank->rankdescr }}"></i>
                </p>
                @else
                <p>None</p>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h2>Divisions</h2>
                <ul class="skills">
                @forelse ($divs as $div)
                <li>
                    <a href="/division/{{ $div->divalias }}">
                        {{ $div->name }}
                    </a>
                </li>
                @empty
                <p>None</p>
                @endforelse
            </ul>
            </div>
            <div class="col-md-6">
                @if ($show_sensitive)
                <h2>Email</h2>
                <p>{{ $knight->email }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h2>Skills</h2>
        <ul class="skills">
        @forelse ($skills as $skill)
            <li>{{ $skill->skillname }}</li>
        @empty
            <li>None</li>
        @endforelse
        </ul>
    </div>
</div>
<div class="row">
    <div class="col">
        <h2>About Me</h2>
        <p>{{ $knight->bio }}</p>
    </div>
    <div class="col">
        @if ($show_irl)
        <h2>Real Life</h2>
        <p>{{ $knight->rlimpact }}</p>
        @endif
    </div>
</div>
@endsection
