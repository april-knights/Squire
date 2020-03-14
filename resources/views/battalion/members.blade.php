@extends("layouts.app")

@section("title", $batt->name)

@section("content")
<div class="row">
    <div class="col">
        <h2>Battalion Name</h2>
        <p>{{ $batt->name }}</p>
    </div>
    <div class="col">
        <h2>Battalion Leader</h2>
        @if ($battlead)
        <a href="/profile/{{ $battlead->rname }}">
            {{ $battlead->rname }}
        </a>
        @else
        <p>No one</p>
        @endif
    </div>
    <div class="col">
        <!-- TODO: battalion banner -->
    </div>
</div>
@component('component.membertable', ['members' => $members])
@endcomponent
@endsection
