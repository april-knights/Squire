@extends('layouts.app')

@section('title')
    Login
@endsection

@section('content')
@if(session()->has('error'))
    <div class="alert alert-error">
        {{ session()->get('error') }}
    </div>
@endif
<div class="row justify-content-md-center">
    <div class="col-md-3">
        <div class="card bg-dark">
            <div class="card-header">Login</div>
            <div class="card-body">
                <h5 class="card-title">Login with Reddit</h5>
                <a class="btn btn-primary" href="/login/reddit">
                    Reddit Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
