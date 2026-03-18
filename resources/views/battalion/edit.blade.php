@extends('layouts.app')

@section('title', 'Edit ' . $batt->name)

@section('content')
<?php /** @var \App\Model\Battalion $batt */ ?>
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<h1>Edit {{ $batt->name }}</h1>
<form method="POST" id="edit" action="/battalion/{{ $batt->battalias }}/edit">
    @csrf
    <div class="row">
        <div class="col-md">
            <div class="form-group">
                <label for="name">Battalion Name</label>
                <input class="form-control" id="name" name="name" type="text"
                    value="{{ $batt->name }}" maxlength="30">
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                <label for="battalias">Alias</label>
                <input class="form-control" id="battalias" name="battalias" type="text"
                    value="{{ $batt->battalias }}" maxlength="10">
                <small class="form-text text-muted">
                    Used in URLs: /battalion/<strong>{{ $batt->battalias }}</strong>
                </small>
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                <label for="color">Color</label>
                <input class="form-control" id="color" name="color" type="text"
                    value="{{ $batt->color }}" maxlength="15">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md">
            <div class="form-group">
                <label for="battlead">Battalion Leader</label>
                <select class="form-control" name="battlead">
                    <option value="" @if(!$batt->battlead) selected @endif>— None —</option>
                    @php /** @var \App\Model\Knight $knight */ @endphp
                    @foreach ($all_knights as $knight)
                    <option value="{{ $knight->pkey }}"
                        @if ($knight->pkey == $batt->battlead) selected @endif>
                        {{ $knight->rname }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                <label for="battsec1">First Officer</label>
                <select class="form-control" name="battsec1">
                    <option value="" @if(!$batt->battsec1) selected @endif>— None —</option>
                    @foreach ($all_knights as $knight)
                    <option value="{{ $knight->pkey }}"
                        @if ($knight->pkey == $batt->battsec1) selected @endif>
                        {{ $knight->rname }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                <label for="battsec2">Second Officer</label>
                <select class="form-control" name="battsec2">
                    <option value="" @if(!$batt->battsec2) selected @endif>— None —</option>
                    @foreach ($all_knights as $knight)
                    <option value="{{ $knight->pkey }}"
                        @if ($knight->pkey == $batt->battsec2) selected @endif>
                        {{ $knight->rname }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="motto">Motto</label>
                <input class="form-control" id="motto" name="motto" type="text"
                    value="{{ $batt->motto }}" maxlength="64">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="battdescr">Description</label>
                <textarea class="form-control" id="battdescr" name="battdescr"
                    maxlength="500">{{ $batt->battdescr }}</textarea>
            </div>
        </div>
    </div>
    <div class="row">
        @if($can_delete)
        <div class="col">
            <div class="form-group">
                <button type="submit" class="btn btn-danger float-left" form="delete"
                    data-toggle="confirmation"
                    data-btn-ok-icon-class="fas fa-check"
                    data-btn-cancel-icon-class="fas fa-ban">Delete Battalion</button>
            </div>
        </div>
        @endif
        <div class="col">
            <button type="submit" class="btn btn-success float-right">Save Changes</button>
        </div>
    </div>
</form>
@if($can_delete)
<form method="POST" id="delete" action="/battalion/{{ $batt->battalias }}">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection
