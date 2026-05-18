@extends('layouts.app')

@section('title', 'Election Results')

@section('content')
<style>
.results-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.results-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.round-header {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 0.75rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #8b3a3a;
}
.candidate-result-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.6rem;
}
.candidate-result-name {
    width: 180px;
    flex-shrink: 0;
    font-size: 0.88rem;
}
.result-bar-wrap {
    flex: 1;
    background-color: #3a1a1a;
    border-radius: 3px;
    height: 18px;
    overflow: hidden;
}
.result-bar {
    height: 100%;
    background-color: #8b3a3a;
    border-radius: 3px;
    transition: width 0.4s ease;
}
.result-bar.winner {
    background-color: #5cb85c;
}
.result-bar.eliminated {
    background-color: #4a2020;
}
.result-count {
    width: 60px;
    text-align: right;
    font-size: 0.82rem;
    color: #c0a0a0;
    flex-shrink: 0;
}
.winner-banner {
    background-color: #2d6a2d;
    border: 1px solid #5cb85c;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    text-align: center;
    color: #efefef;
}
.winner-banner h3 {
    color: #5cb85c;
    margin-bottom: 0.25rem;
}
.eliminated-tag {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #c0392b;
    margin-left: 0.5rem;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-chart-pie mr-2"></i>Results — {{ $election->election_year }}
    </h4>
    <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Dashboard
    </a>
</div>

@if($results['error'])
<div style="background:#4a1f1f;border:1px solid #8b3a3a;border-radius:4px;padding:0.75rem 1rem;color:#f0ad4e;margin-bottom:1rem;">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $results['error'] }}
</div>
@endif

@if($results['winner'])
@php $winner = $candidates[$results['winner']] ?? null; @endphp
@if($winner)
<div class="winner-banner">
    <i class="fas fa-crown" style="font-size:1.75rem;color:#f0ad4e;margin-bottom:0.5rem;display:block;"></i>
    <h3>{{ $winner->knight->kname }}</h3>
    <p style="color:#c0a0a0;margin:0;">/u/{{ $winner->knight->rname }} — {{ $election->election_year }} Grandmaster</p>
</div>
@endif
@endif

@foreach($results['rounds'] as $round)
<div class="results-card">
    <div class="round-header">
        Round {{ $round['round'] }}
        @if($round['winner'])
            — <span style="color:#5cb85c;">Winner declared</span>
        @elseif(!empty($round['eliminated']))
            — Eliminated:
            @foreach((array)$round['eliminated'] as $elimPkey)
                <span style="color:#c0392b;">{{ $candidates[$elimPkey]->knight->kname ?? 'Unknown' }}</span>
            @endforeach
        @endif
    </div>

    @php $total = $round['total']; @endphp

    @foreach($round['counts'] as $candidatePkey => $count)
    @php
        $candidate  = $candidates[$candidatePkey] ?? null;
        $pct        = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        $isWinner   = $round['winner'] == $candidatePkey;
        $isEliminated = in_array($candidatePkey, (array)$round['eliminated']);
    @endphp
    <div class="candidate-result-row">
        <div class="candidate-result-name">
            {{ $candidate?->knight?->kname ?? 'Unknown' }}
            @if($isEliminated)
                <span class="eliminated-tag">Out</span>
            @endif
        </div>
        <div class="result-bar-wrap">
            <div class="result-bar {{ $isWinner ? 'winner' : ($isEliminated ? 'eliminated' : '') }}"
                 style="width:{{ $pct }}%"></div>
        </div>
        <div class="result-count">{{ $count }} ({{ $pct }}%)</div>
    </div>
    @endforeach

    <div style="font-size:0.78rem;color:#c0a0a0;margin-top:0.5rem;">
        Total votes in round: {{ $total }}
        @if(!empty($results['fail_count']) && $round['round'] === 1)
            &bull; {{ $results['fail_count'] }} ballot(s) failed decryption
        @endif
    </div>
</div>
@endforeach

@if($election->phase === 'complete')
<div class="results-card">
    <h5><i class="fas fa-archive mr-1"></i> Archive Encryption Key</h5>
    <p style="color:#c0a0a0;font-size:0.85rem;margin-bottom:0.75rem;">
        Archive your passphrase so future administrators can audit this election.
        It will be encrypted with the application key before storage.
    </p>
    <form method="POST" action="{{ route('election.archive-key') }}">
        @csrf
        <div style="margin-bottom:0.75rem;">
            <input type="password" name="passphrase" class="form-control form-control-sm"
                style="background:#3a1a1a;border-color:#8b3a3a;color:#efefef;max-width:340px;"
                placeholder="Your passphrase" required autocomplete="off">
        </div>
        <div style="margin-bottom:0.75rem;">
            <input type="text" name="note" class="form-control form-control-sm"
                style="background:#3a1a1a;border-color:#8b3a3a;color:#efefef;max-width:340px;"
                placeholder="Optional note...">
        </div>
        <button type="submit" class="btn btn-sm"
            style="background:#8b3a3a;border-color:#a04040;color:#efefef;">
            <i class="fas fa-archive mr-1"></i> Archive Key
        </button>
    </form>
</div>
@endif
@endsection