@extends('layouts.app')
@section('title', $batt->name)
@section('content')
@component('component.battoverview', ['batt' => $batt])
@endcomponent
<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="memberSearch" class="form-control" placeholder="Search by name, Discord ID, or skill...">
    </div>
</div>
@component('component.membertable', [
    'members'   => $members,
    'sort'      => $sort,
    'direction' => $direction,
    'alias'     => $batt->battalias,
])
@endcomponent
@endsection