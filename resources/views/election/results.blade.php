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
}
.result-bar.winner     { background-color: #5cb85c; }
.result-bar.eliminated { background-color: #4a2020; }
.result-count {
    width: 90px;
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
.winner-banner h3 { color: #5cb85c; margin-bottom: 0.25rem; }
.eliminated-tag {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #c0392b;
    margin-left: 0.5rem;
}
.tie-notice {
    background-color: #4a2020;
    border: 1px solid #f0ad4e;
    border-radius: 4px;
    padding: 0.5rem 0.75rem;
    color: #f0ad4e;
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
}
.alert-box {
    background:#4a1f1f;
    border:1px solid #8b3a3a;
    border-radius:4px;
    padding:0.75rem 1rem;
    color:#f0ad4e;
    margin-bottom:1rem;
}
</style>

@php
    $error      = $results['error'] ?? null;
    $failCount  = $results['fail_count'] ?? 0;
    $rounds     = $results['rounds'] ?? [];
    $ballotsIn  = $rounds[0]['total'] ?? 0;
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-chart-pie mr-2"></i>Results — {{ $election->election_year }}
    </h4>
    <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Dashboard
    </a>
</div>

<div class="results-card" style="padding:0.75rem 1.25rem;">
    <span style="font-size:0.85rem;">
        <strong>{{ $ballotsIn }}</strong> ballot(s) counted
        @if($failCount > 0)
            &bull; <span style="color:#f0ad4e;"><strong>{{ $failCount }}</strong> failed to decrypt — do not announce until resolved</span>
        @endif
    </span>
    <div style="font-size:0.75rem;color:#c0a0a0;margin-top:0.25rem;">
        Results are computed from your passphrase on each view and never stored. A winner needs more than 50% of the ballots still in play.
        Reloading this page will ask for your passphrase again.
    </div>
</div>

@if($error)
<div class="alert-box">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
</div>
@endif

@if(! empty($results['winner']))
@php $winner = $candidates[$results['winner']] ?? null; @endphp
@if($winner)
<div class="winner-banner">
    <i class="fas fa-crown" style="font-size:1.75rem;color:#f0ad4e;margin-bottom:0.5rem;display:block;"></i>
    <h3>/u/{{ $winner->knight->rname }}</h3>
    <p style="color:#c0a0a0;margin:0;">{{ $election->election_year }} Grandmaster — pending announcement and a passing audit</p>
</div>
@endif
@endif

@foreach($rounds as $round)
@php
    $total      = $round['total'];
    $elimList   = (array) ($round['eliminated'] ?? []);
    $needed     = $total > 0 ? intdiv($total, 2) + 1 : 0;
@endphp
<div class="results-card">
    <div class="round-header">
        Round {{ $round['round'] }}
        @if($round['winner'])
            — <span style="color:#5cb85c;">Winner declared</span>
        @elseif(! empty($elimList))
            — Eliminated:
            @foreach($elimList as $elimPkey)
                <span style="color:#c0392b;">/u/{{ $candidates[$elimPkey]->knight->rname ?? 'Unknown' }}</span>
            @endforeach
        @endif
        <span style="float:right;text-transform:none;letter-spacing:0;">majority needed: {{ $needed }}</span>
    </div>

    @if(count($elimList) > 1)
    <div class="tie-notice">
        <i class="fas fa-balance-scale mr-1"></i>
        Tie for last place: the tied candidates were eliminated together. Review this round before announcing a result.
    </div>
    @endif

    @foreach($round['counts'] as $candidatePkey => $count)
    @php
        $candidate    = $candidates[$candidatePkey] ?? null;
        $pct          = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        $isWinner     = $round['winner'] == $candidatePkey;
        $isEliminated = in_array($candidatePkey, $elimList);
    @endphp
    <div class="candidate-result-row">
        <div class="candidate-result-name">
            /u/{{ $candidate?->knight?->rname ?? 'Unknown' }}
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
        Ballots in play this round: {{ $total }}
    </div>
</div>
@endforeach

<div class="results-card">
    <h5><i class="fas fa-clipboard-check mr-1"></i> Before you announce</h5>
    <ol style="font-size:0.85rem;color:#efefef;padding-left:1.2rem;margin:0;">
        <li>Run the audit from the dashboard — results are not valid until it passes.</li>
        <li>Resolve any ballots that failed to decrypt.</li>
        <li>Announce, then archive the encryption key
            @if($keyArchived ?? false)
                <span style="color:#5cb85c;">(already archived)</span>
            @endif
            from the dashboard.</li>
    </ol>
</div>
@endsection