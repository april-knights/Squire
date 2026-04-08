@extends('layouts.app')
<?php /** @var \App\Model\Knight $knight */ ?>

@if ($knight->rank)
    @section('title', $knight->rank->name . ' ' . $knight->rname)
@else
    @section('title', $knight->rname)
@endif

@section('content')
<div class="row">
    <div class="col-md-4">
        <h2>Reddit Name</h2>
        <p>/u/{{ $knight->rname }}</p>
    </div>
    <div class="col-md-4">
        <h2>Discord Name</h2>
        <p>{{ $knight->dname }}</p>
    </div>
    <div class="col-md-3">
        @if ($show_sensitive)
        <h2>
            Knight ID
            <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="Uniquely identifies you. Only visible to councillors and you."></i>
        </h2>
        <p>{{ $knight->knum }}</p>
        @endif
    </div>
    @if($can_edit)
    <div class="col-md-1">
        <a href="/profile/{{ $knight->rname }}/edit"><i class="fas fa-edit"></i></a>
    </div>
    @endif
</div>
<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h2>Battalion</h2>
                @if($knight->battalion)
                <a href="/battalion/{{ $knight->battalion->battalias }}">{{ $knight->battalion->name }}</a>
                @else
                <p>None</p>
                @endif
            </div>
            <div class="col-md-6">
                <h2>Rank</h2>
                @if($knight->rank)
                <p>
                    {{ $knight->rank->name }}
                    <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{ $knight->rank->rankdescr }}"></i>
                </p>
                @else
                <p>None</p>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h2>Divisions</h2>
                <ul class="skills">
                @forelse ($knight->divisions as $div)
                <?php /** @var \App\Model\Division $div */ ?>
                <li>
                    <a href="/division/{{ $div->divalias }}">
                        {{ $div->name }}
                    </a>
                </li>
                @empty
                <p>None</p>
                @endforelse
            </ul>
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
        <ul class="skills" id="skills-list">
        @forelse ($knight->skills as $index => $skill)
            <?php /** @var \App\Model\Skill $skill */ ?>
            <li @if($index >= 5) style="display:none;" class="skill-extra" @endif>
                {{ $skill->skillname }}
            </li>
        @empty
            <li>None</li>
        @endforelse
        </ul>
        @if($knight->skills->count() > 5)
        <button
            type="button"
            id="skills-toggle"
            onclick="toggleSkills()"
            style="background:none; border:none; padding:0; color:inherit; cursor:pointer; font-size:0.875rem; text-decoration:underline;"
        >
            Show more
        </button>
        <script>
            function toggleSkills() {
                var extras = document.querySelectorAll('.skill-extra');
                var btn = document.getElementById('skills-toggle');
                var expanded = btn.dataset.expanded === 'true';
                extras.forEach(function(el) { el.style.display = expanded ? 'none' : ''; });
                btn.dataset.expanded = expanded ? 'false' : 'true';
                btn.textContent = expanded ? 'Show more' : 'Show less';
            }
        </script>
        @endif
    </div>
</div>
@if($featured_badges->isNotEmpty())
<div class="row">
    <div class="col">
        <h2>Badges</h2>
        <div class="d-flex flex-wrap">
            @foreach($featured_badges as $badge)
            <div class="mr-2 mb-2">
                <img src="{{ asset($badge->imgurl ?? 'static/img/badges/NoArtYet.jpg') }}"
                    width="64" alt="{{ $badge->bdg_title }}"
                    title="{{ $badge->bdg_title }}"
                    class="img-fluid">
            </div>
            @endforeach
        </div>
        <a class="font-italic" href="/profile/{{ $knight->rname }}/badges">See all…</a>
    </div>
</div>
@endif
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
@if ($show_officer_fields)
<div class="row">
    <div class="col-md-6">
        <h2>Discord ID</h2>
        <p>
            {{ $knight->discordid ?? 'Not set' }}
            @if ($knight->discordid)
            <button class="btn btn-sm btn-secondary ml-2" onclick="navigator.clipboard.writeText('{{ $knight->discordid }}')">
                <i class="fas fa-copy"></i>
            </button>
            @endif
        </p>
    </div>
    <div class="col-md-6">
        <h2>Interview Transcript</h2>
        @if ($knight->inttrans)
        <p><a href="{{ $knight->inttrans }}" target="_blank">Interview Transcript</a></p>
        @else
        <p>No transcript available</p>
        @endif
    </div>
</div>
@if ($show_onote)
<div class="row">
    <div class="col">
        <h2>Officer Note</h2>
        <p>{{ $knight->onote ?? 'None' }}</p>
    </div>
</div>
@endif
@endif
@endsection