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
        <p>No one</p>
        @endif
    </div>
    <div class="col">
        <!-- TODO: division banner? -->
    </div>
</div>

@component('component.membertable', ['members' => $members])
@endcomponent

@endsection
