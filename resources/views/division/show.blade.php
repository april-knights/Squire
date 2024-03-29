@extends('layouts.app')

@section('title', $div->name)

@section('content')
<div class="row">
    <div class="col">
        <h2>Division Name</h2>
        <p>{{ $div->name }}</p>
    </div>
    <div class="col">
        <h2>Division Leader</h2>
        @if ($divlead)
        <a href="/profile/{{ $divlead->rname }}">
            {{ $divlead->rname }}
        </a>
        @else
        <p>No one</a>
        @endif
    </div>
        <div class="col">
        <img class="banner" src="{{ asset('static/img/div_banners/' . $div->divalias . '.png') }}"
             alt="{{ $div->name }} Banner" title="{{ $div->name }} Banner">
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h2>Division Motto</h2>
                <p>{{ $div->motto }}
            </div>
            <div class="col-md-6">
                <h2>Division Officers</h2>
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
        <h2>Division Members</h2>
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
        <a class="font-italic" href="/division/{{ $div->divalias }}/members">See all…</a>
    </div>
</div>
<div class="row">
    <div class="col">
        <h2>Division Description</h2>
        <p>{{ $div->divdescr }}</p>
    </div>
</div>
@endsection
