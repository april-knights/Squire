@extends("layouts.app")

@section("title", "Home")

@section("content")
    <div class="row">
        <div class="col">
            <h2>Reddit Name</h2>
            {{ $knight->rname }}
        </div>
        <div class="col">
            <h2>Discord Name</h2>
            {{ $knight->dname }}
        </div>
        <div class="col">
            <h2>Knight ID</h2>
            {{ $knight->knum }}
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <h2>Battalion</h2>
                </div>
                <div class="col-md-6">
                    <h2>Rank</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2>Divisions</h2>
                </div>
                <div class="col-md-6">
                    <h2>Email</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h2>Skills</h2>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>About Me</h2>
            {{ $knight->bio }}
        </div>
        <div class="col">
            <h2>Real Life</h2>
            {{ $knight->rlimpact }}
        </div>
    </div>
@endsection
