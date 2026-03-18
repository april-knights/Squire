@extends('layouts.app')
@section('title', 'Edit ' . $batt->name)
<?php /** @var \App\Model\Battalion $batt */ ?>

@section('content')
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
                    <option value="" @if(!$batt->battlead) selected @endif>— None —</o
