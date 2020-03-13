@extends("layouts.app")

@section("title", $rank->name . " " . $knight->rname)

@section("content")
    <div class="row">
        <div class="col">
            <h2>Reddit Name</h2>
            <p>{{ $knight->rname }}</p>
        </div>
        <div class="col">
            <h2>Discord Name</h2>
            <p>{{ $knight->dname }}</p>
        </div>
        <div class="col">
            @if ($show_sensitive)
            <h2>Knight ID</h2>
            <p>{{ $knight->knum }}</p>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <h2>Battalion</h2>
                    @if($batt)
                    <a href="/battalion/{{ $batt->battalias }}">{{ $batt->name }}</a>
                    @else
                    <p>None</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h2>Rank</h2>
                    @if($rank)
                    <p>{{ $rank->name }}</p>
                    @else
                    <p>None</p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2>Divisions</h2>
                </div>
                <div class="col-md-6">
                    @if ($show_sensitive)
                    <h2>Email</h2>
                    <p>{{ $knight->email }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h2>Skills</h2>
            <ul class="skills">
            @forelse ($skills as $skill)
                <li>{{ $skill->skillname }}</li>
            @empty
                <li>None</li>
            @endforelse
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>About Me</h2>
            <p>{{ $knight->bio }}</p>
        </div>
        <div class="col">
            @if ($show_irl)
            <h2>Real Life</h2>
            <p>{{ $knight->rlimpact }}</p>
            @endif
        </div>
    </div>
@endsection
