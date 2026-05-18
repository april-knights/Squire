@extends('layouts.app')

@section('title', 'EA Dashboard')

@section('content')
<style>
.ea-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.ea-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.ea-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.ea-stat-box {
    background-color: #5a2424;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.75rem 0.5rem;
    text-align: center;
}
.ea-stat-box .stat-number {
    font-size: 1.6rem;
    font-weight: bold;
    line-height: 1;
    color: #f0ad4e;
}
.ea-stat-box .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #c0a0a0;
    margin-top: 0.25rem;
}
.phase-timeline {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1rem;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}
.phase-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 80px;
    position: relative;
}
.phase-step::before {
    content: '';
    position: absolute;
    top: 14px;
    right: 50%;
    left: -50%;
    height: 2px;
    background-color: #4a2020;
    z-index: 0;
}
.phase-step:first-child::before {
    display: none;
}
.phase-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #4a2020;
    border: 2px solid #8b3a3a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    position: relative;
    z-index: 1;
    color: #c0a0a0;
}
.phase-dot.complete {
    background-color: #5cb85c;
    border-color: #5cb85c;
    color: #fff;
}
.phase-dot.current {
    background-color: #8b3a3a;
    border-color: #f0ad4e;
    color: #f0ad4e;
}
.phase-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #c0a0a0;
    margin-top: 0.35rem;
    text-align: center;
}
.phase-label.current {
    color: #f0ad4e;
    font-weight: bold;
}
.btn-ea {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.4rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.15s ease;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}
.btn-ea:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
.btn-ea.btn-danger-ea {
    background-color: #6b2b2b;
    border-color: #8b3a3a;
}
.btn-ea.btn-danger-ea:hover {
    background-color: #8b3a3a;
}
.btn-ea:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.ea-form-group {
    margin-bottom: 0.75rem;
}
.ea-form-group label {
    font-size: 0.8rem;
    color: #c0a0a0;
    display: block;
    margin-bottom: 0.3rem;
}
.ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    width: 100%;
}
.ea-input:focus {
    outline: none;
    border-color: #efefef;
}
.ea-textarea {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    width: 100%;
    min-height: 100px;
    resize: vertical;
}
.phase-log-row {
    display: flex;
    gap: 1rem;
    font-size: 0.82rem;
    padding: 0.4rem 0;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.phase-log-row:last-child {
    border-bottom: none;
}
.phase-log-time {
    color: #c0a0a0;
    white-space: nowrap;
    flex-shrink: 0;
}
.warning-banner {
    background-color: #4a2020;
    border: 1px solid #f0ad4e;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    color: #f0ad4e;
    font-size: 0.88rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
</style>

@php
    $phases  = \App\Model\Election::PHASES;
    $current = array_search($election->phase, $phases);
    $isFullEA = $eaRecord && $eaRecord->isFullEA();
@endphp

{{-- Admin test mode banner --}}
@if($isAdminTest)
<div class="warning-banner">
    <i class="fas fa-flask"></i>
    <span>Admin test mode active — you are viewing the EA dashboard as an administrator. Toggle off in election settings when done.</span>
</div>
@endif

{{-- Voting paused banner --}}
@if($election->phase === 'voting' && $election->voting_paused)
<div class="warning-banner">
    <i class="fas fa-pause-circle"></i>
    <span>Voting is currently paused. Knights cannot submit ballots until you authenticate with your passphrase.</span>
</div>
@endif

<div class="row">
    <div class="col-lg-8">

        {{-- Phase timeline --}}
        <div class="ea-card">
            <h5><i class="fas fa-map-signs mr-1"></i> Election Phase</h5>
            <div class="phase-timeline">
                @foreach($phases as $i => $phase)
                <div class="phase-step">
                    <div class="phase-dot {{ $i < $current ? 'complete' : ($i === $current ? 'current' : '') }}">
                        @if($i < $current)
                            <i class="fas fa-check"></i>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <div class="phase-label {{ $i === $current ? 'current' : '' }}">
                        {{ ucfirst($phase) }}
                    </div>
                </div>
                @endforeach
            </div>

            @if($election->phase_deadline)
            <div style="font-size:0.82rem;color:#c0a0a0;margin-top:0.5rem;">
                <i class="fas fa-calendar-alt mr-1"></i>
                Phase deadline: {{ $election->phase_deadline->format('F j, Y') }}
            </div>
            @endif
        </div>

        {{-- Stats --}}
        <div class="ea-card">
            <h5><i class="fas fa-chart-bar mr-1"></i> Election at a Glance</h5>
            <div class="ea-stat-grid">
                <div class="ea-stat-box">
                    <div class="stat-number">{{ $candidates->where('status','accepted')->count() }}</div>
                    <div class="stat-label">Candidates</div>
                </div>
                <div class="ea-stat-box">
                    <div class="stat-number">{{ $registeredCount }}</div>
                    <div class="stat-label">Registered</div>
                </div>
                <div class="ea-stat-box">
                    <div class="stat-number">{{ $voteCount }}</div>
                    <div class="stat-label">Votes Cast</div>
                </div>
                @if($registeredCount > 0)
                <div class="ea-stat-box">
                    <div class="stat-number">{{ round(($voteCount / $registeredCount) * 100) }}%</div>
                    <div class="stat-label">Turnout</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Candidate list --}}
        <div class="ea-card">
            <h5><i class="fas fa-users mr-1"></i> Candidates</h5>
            @forelse($candidates as $candidate)
            <div style="background:#5a2424;border:1px solid #8b3a3a;border-radius:4px;padding:0.75rem 1rem;margin-bottom:0.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <div>
                        <strong>{{ $candidate->knight->kname }}</strong>
                        <span style="color:#c0a0a0;font-size:0.82rem;margin-left:0.5rem;">/u/{{ $candidate->knight->rname }}</span>
                    </div>
                    <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em;padding:0.15rem 0.5rem;border-radius:3px;
                        background:{{ $candidate->status === 'accepted' ? '#2d6a2d' : ($candidate->status === 'declined' || $candidate->status === 'withdrawn' ? '#5a2424' : '#4a3a1a') }};
                        color:{{ $candidate->status === 'accepted' ? '#5cb85c' : ($candidate->status === 'declined' || $candidate->status === 'withdrawn' ? '#c0a0a0' : '#f0ad4e') }}">
                        {{ ucfirst($candidate->status) }}
                    </span>
                </div>
                @if($candidate->nominations->count())
                <div style="margin-top:0.5rem;font-size:0.78rem;color:#c0a0a0;">
                    Nominated by {{ $candidate->nominations->where('action','nominated')->first()?->knight?->kname ?? '—' }}
                    &bull; {{ $candidate->nominations->where('action','seconded')->count() }} second(s)
                </div>
                @endif
            </div>
            @empty
            <p style="color:#c0a0a0;font-size:0.88rem;">No candidates added yet.</p>
            @endforelse

            @if($isFullEA)
            <div style="margin-top:0.75rem;">
                <a href="{{ route('admin.elections.show', $election->pkey) }}" class="btn-ea">
                    <i class="fas fa-edit mr-1"></i> Manage Candidates
                </a>
            </div>
            @endif
        </div>

        {{-- Phase log --}}
        <div class="ea-card">
            <h5><i class="fas fa-history mr-1"></i> Phase Log</h5>
            @forelse($phaseLog as $log)
            <div class="phase-log-row">
                <span class="phase-log-time">{{ $log->crtsetdt->format('M j, Y g:ia') }}</span>
                <span>
                    @if($log->from_phase)
                        {{ ucfirst($log->from_phase) }} → {{ ucfirst($log->to_phase) }}
                    @else
                        Election created ({{ ucfirst($log->to_phase) }})
                    @endif
                    @if($log->note)
                        <span style="color:#c0a0a0;"> — {{ $log->note }}</span>
                    @endif
                </span>
            </div>
            @empty
            <p style="color:#c0a0a0;font-size:0.88rem;">No phase transitions yet.</p>
            @endforelse
        </div>

    </div>

    <div class="col-lg-4">

        {{-- Quick links --}}
        <div class="ea-card">
            <h5><i class="fas fa-link mr-1"></i> Quick Links</h5>
            <a href="{{ route('election.voters') }}" class="btn-ea" style="display:block;text-align:center;margin-bottom:0.4rem;">
                <i class="fas fa-list mr-1"></i> Voter List
            </a>
            @if(in_array($election->phase, ['counting','complete']))
            <a href="{{ route('election.results') }}" class="btn-ea" style="display:block;text-align:center;margin-bottom:0.4rem;">
                <i class="fas fa-chart-pie mr-1"></i> Results
            </a>
            @endif
            @if($isFullEA)
            <a href="{{ route('election.audit') }}" class="btn-ea" style="display:block;text-align:center;margin-bottom:0.4rem;">
                <i class="fas fa-upload mr-1"></i> Upload Audit CSV
            </a>
            @endif
            @if($isFullEA || $isAdminTest)
            <a href="{{ route('admin.elections.show', $election->pkey) }}" class="btn-ea" style="display:block;text-align:center;">
                <i class="fas fa-cog mr-1"></i> Admin Election Panel
            </a>
            @endif
        </div>

        {{-- Reddit threads --}}
        <div class="ea-card">
            <h5><i class="fas fa-reddit mr-1"></i> Reddit Threads</h5>

            @if($election->nomination_thread_url)
            <div style="margin-bottom:0.5rem;">
                <a href="{{ $election->nomination_thread_url }}" target="_blank" class="btn-ea" style="width:100%;text-align:center;display:block;">
                    <i class="fas fa-external-link-alt mr-1"></i> Nomination Thread
                </a>
            </div>
            @endif

            @if($election->debate_thread_url)
            <div style="margin-bottom:0.5rem;">
                <a href="{{ $election->debate_thread_url }}" target="_blank" class="btn-ea" style="width:100%;text-align:center;display:block;">
                    <i class="fas fa-external-link-alt mr-1"></i> Debate Thread
                </a>
            </div>
            @endif

            @if($isFullEA && $redditAuthorized)
                @if($election->phase === 'nominations' && !$election->nomination_post_id)
                <form method="POST" action="{{ route('election.nomination-thread') }}" style="margin-top:0.75rem;">
                    @csrf
                    <div class="ea-form-group">
                        <label>Thread Title</label>
                        <input type="text" name="title" class="ea-input"
                            value="{{ $election->election_year }} Grandmaster Election — Nominations" required>
                    </div>
                    <div class="ea-form-group">
                        <label>Thread Body</label>
                        <textarea name="body" class="ea-textarea" required>Nominations for the {{ $election->election_year }} Grandmaster election are now open.</textarea>
                    </div>
                    <button type="submit" class="btn-ea" style="width:100%;text-align:center;">
                        <i class="fas fa-paper-plane mr-1"></i> Post Nomination Thread
                    </button>
                </form>
                @endif

                @if($election->phase === 'debate' && !$election->debate_post_id)
                <form method="POST" action="{{ route('election.debate-thread') }}" style="margin-top:0.75rem;">
                    @csrf
                    <div class="ea-form-group">
                        <label>Thread Title</label>
                        <input type="text" name="title" class="ea-input"
                            value="{{ $election->election_year }} Grandmaster Election — Debate & Q&A" required>
                    </div>
                    <div class="ea-form-group">
                        <label>Thread Body</label>
                        <textarea name="body" class="ea-textarea" required>The debate and Q&A period for the {{ $election->election_year }} Grandmaster election is now open. Candidates will be tagged below.</textarea>
                    </div>
                    <button type="submit" class="btn-ea" style="width:100%;text-align:center;">
                        <i class="fas fa-paper-plane mr-1"></i> Post Debate Thread
                    </button>
                </form>
                @endif
            @elseif($isFullEA && !$redditAuthorized)
            <p style="color:#f0ad4e;font-size:0.82rem;">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                AKSquire2 is not authorized. <a href="{{ route('admin.elections.reddit-auth') }}" style="color:#f0ad4e;">Authorize here.</a>
            </p>
            @endif
        </div>

        {{-- Voting controls (full EA only) --}}
        @if($isFullEA && $election->phase === 'voting')
        <div class="ea-card">
            <h5><i class="fas fa-key mr-1"></i> Voting Controls</h5>
            @if($election->voting_paused)
            <form method="POST" action="{{ route('election.open-voting') }}">
                @csrf
                <div class="ea-form-group">
                    <label>Your Passphrase</label>
                    <input type="password" name="passphrase" class="ea-input" required autocomplete="off">
                </div>
                <button type="submit" class="btn-ea" style="width:100%;text-align:center;">
                    <i class="fas fa-play mr-1"></i> Resume Voting
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('election.pause-voting') }}">
                @csrf
                <button type="submit" class="btn-ea btn-danger-ea" style="width:100%;text-align:center;">
                    <i class="fas fa-pause mr-1"></i> Pause Voting
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Phase advancement (full EA only) --}}
        @if($isFullEA && $election->phase !== 'complete')
        <div class="ea-card">
            <h5><i class="fas fa-forward mr-1"></i> Advance Phase</h5>
            <form method="POST" action="{{ route('election.advance') }}">
                @csrf
                @if($election->phase === 'voting')
                <div class="ea-form-group">
                    <label>Passphrase (required to close voting)</label>
                    <input type="password" name="passphrase" class="ea-input" required autocomplete="off">
                </div>
                @endif
                <div class="ea-form-group">
                    <label>Note (optional)</label>
                    <input type="text" name="note" class="ea-input" placeholder="Reason for advancing...">
                </div>
                @php
                    $nextPhase = \App\Model\Election::PHASES[array_search($election->phase, \App\Model\Election::PHASES) + 1] ?? null;
                @endphp
                @if($nextPhase)
                <button type="submit" class="btn-ea" style="width:100%;text-align:center;"
                    onclick="return confirm('Advance election to {{ ucfirst($nextPhase) }} phase?')">
                    <i class="fas fa-arrow-right mr-1"></i> Advance to {{ ucfirst($nextPhase) }}
                </button>
                @endif
            </form>
        </div>
        @endif

        {{-- EA Dashboard Settings (full EA only) --}}
        @if($isFullEA)
        <div class="ea-card">
            <h5><i class="fas fa-sliders-h mr-1"></i> Dashboard Settings</h5>
            <form method="POST" action="{{ route('election.toggles') }}">
                @csrf
                <div style="margin-bottom:0.75rem;">
                    @include('partials.toggle', [
                        'name'    => 'silent_audit_dms',
                        'checked' => $eaRecord->silent_audit_dms,
                        'label'   => 'Silent audit DMs',
                    ])
                </div>
                <div style="margin-bottom:1rem;">
                    @include('partials.toggle', [
                        'name'    => 'show_voter_names_secondary_officers',
                        'checked' => $eaRecord->show_voter_names_secondary_officers,
                        'label'   => 'Show voter names to secondary officers',
                    ])
                </div>
                <button type="submit" class="btn-ea" style="width:100%;text-align:center;">
                    Save Settings
                </button>
            </form>
        </div>
        @endif

        {{-- Admin test mode toggle --}}
        @if($isAdminTest || ($isFullEA && $election->admin_test_mode !== null))
        <div class="ea-card">
            <h5><i class="fas fa-flask mr-1"></i> Admin Test Mode</h5>
            <form method="POST" action="{{ route('admin.elections.toggle-test-mode', $election->pkey) }}">
                @csrf
                @include('partials.toggle', [
                    'name'    => 'admin_test_mode',
                    'checked' => $election->admin_test_mode,
                    'label'   => 'Allow Admin full EA access',
                ])
                <button type="submit" class="btn-ea" style="width:100%;text-align:center;margin-top:0.75rem;">
                    Save
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection