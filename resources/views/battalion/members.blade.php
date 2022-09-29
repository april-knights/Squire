@extends('layouts.app')

@section('title', $batt->name)

@section('content')
@component('component.battoverview', ['batt' => $batt])
@endcomponent

@component('component.membertable', ['members' => $batt->members])
@endcomponent
@endsection
