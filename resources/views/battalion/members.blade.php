@extends("layouts.app")

@section("title", $batt->name)

@section("content")
@component('component.battoverview', ['batt' => $batt, 'battlead' => $battlead])
@endcomponent

@component('component.membertable', ['members' => $members])
@endcomponent
@endsection
