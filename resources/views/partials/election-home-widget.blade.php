<style>
.election-widget {
    margin-bottom: 1.5rem;
}
.election-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    color: #efefef;
}
.election-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 0.75rem;
}
.election-status-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}
.election-status-row:last-child {
    margin-bottom: 0;
}
.status-icon {
    font-size: 1rem;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}
.status-icon.ok    { color: #5cb85c; }
.status-icon.warn  { color: #f0ad4e; }
.status-icon.info  { color: #5bc0de; }
.election-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 0.75rem;
}
.election-stat-box {
    background-color: #5a2424;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.6rem 0.5rem;
    text-align: center;
}
.election-stat-box .stat-number {
    font-size: 1.4rem;
    font-weight: bold;
    line-height: 1;
    color: #f0ad4e;
}
.election-stat-box .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #c0a0a0;
    margin-top: 0.2rem;
}
.election-voter-table {
    width: 100%;
    font-size: 0.85rem;
    margin-top: 0.75rem;
    border-collapse: collapse;
}
.election-voter-table th {
    color: #c0a0a0;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.3rem 0.5rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.election-voter-table td {
    padding: 0.3rem 0.5rem;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.election-voter-table tr:last-child td {
    border-bottom: none;
}
.voted-yes { color: #5cb85c; }
.voted-no  { color: #c0a0a0; }
.btn-election {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    font-size: 0.85rem;
    padding: 0.35rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    margin-top: 0.5rem;
    transition: background-color 0.15s ease;
}
.btn-election:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
.phase-badge {
    display: inline-block;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.2rem 0.6rem;
    border-radius: 3px;
    background-color: #8b3a3a;
    color: #efefef;
    margin-left: 0.5rem;
    vertical-align: middle;
}
</style>

<div class="election-widget">

    {{-- Oath status card — always visible year-round --}}
    <div class="election-card">
        <h5><i class="fas fa-scroll mr-1"></i> Annual Oath</h5>

        <div class="election-status-row">
            @if($oath && $oath->verified)
                <span class="status-icon ok"><i class="fas fa-check-circle"></i></span>
                <span>Oath sworn for {{ $oathYear }}</span>
            @elseif($oath && !$oath->verified)
                <span class="status-icon warn"><i class="fas fa-exclamation-circle"></i></span>
                <span>
                    Oath submitted but not yet verified.
                    <form method="POST" action="{{ route('oath.reverify') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-election" style="padding:0.15rem 0.6rem;margin-top:0;">Re-verify</button>
                    </form>
                </span>
            @else
                <span class="status-icon warn"><i class="fas fa-times-circle"></i></span>
                <span>
                    No oath on record for {{ $oathYear }}.
                    @if(\App\Model\Setting::get('oath_thread_url'))
                        <a href="{{ \App\Model\Setting::get('oath_thread_url') }}" target="_blank" class="btn-election" style="padding:0.15rem 0.6rem;margin-top:0;">
                            View oath thread
                        </a>
                    @endif
                </span>
            @endif
        </div>

        {{-- Oath submit form --}}
        @if(!$oath)
        <div style="margin-top:0.75rem;">
            <form method="POST" action="{{ route('oath.store') }}">
                @csrf
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input
                        type="url"
                        name="comment_url"
                        class="form-control form-control-sm"
                        style="max-width:340px;background:#3a1a1a;border-color:#8b3a3a;color:#efefef;"
                        placeholder="Paste your oath comment URL..."
                        required
                    >
                    <button type="submit" class="btn-election" style="margin-top:0;">Submit Oath</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- Election section — only when active election exists --}}
    @if($election)

    {{-- EA / Assistant EA prompt --}}
    @if($isEA || $isAssistantEA)
    <div class="election-card">
        <h5><i class="fas fa-crown mr-1"></i> Election Administrator</h5>
        <div class="election-status-row">
            <span class="status-icon info"><i class="fas fa-tasks"></i></span>
            <span>
                Active election — Phase: <strong>{{ ucfirst($election->phase) }}</strong>
                @if($election->voting_paused && $election->phase === 'voting')
                    <span class="phase-badge" style="background-color:#c0392b;">Voting Paused</span>
                @endif
            </span>
        </div>
        <a href="{{ route('election.dashboard') }}" class="btn-election">
            <i class="fas fa-tachometer-alt mr-1"></i> Go to EA Dashboard
        </a>
    </div>
    @endif

    {{-- Knight election status card --}}
    <div class="election-card">
        <h5><i class="fas fa-vote-yea mr-1"></i> Election {{ $election->election_year }}</h5>

        {{-- Registration status — visible all phases except complete --}}
        @if($election->phase !== 'complete')
        <div class="election-status-row">
            @if($registered)
                <span class="status-icon ok"><i class="fas fa-check-circle"></i></span>
                <span>Registered to vote</span>
            @else
                <span class="status-icon warn"><i class="fas fa-times-circle"></i></span>
                <span>Not registered to vote</span>
            @endif
        </div>

        @if(!$registered)
        <form method="POST" action="{{ route('election.register') }}" style="margin-top:0.5rem;">
            @csrf
            <button type="submit" class="btn-election">Register to Vote</button>
        </form>
        @endif
        @endif

        {{-- Vote prompt --}}
        @if($election->phase === 'voting' && $election->isVotingOpen())
            @if($registered && $oath && $oath->verified)
                @if($hasVoted)
                <div class="election-status-row" style="margin-top:0.75rem;">
                    <span class="status-icon ok"><i class="fas fa-check-circle"></i></span>
                    <span>Your ballot has been submitted. Thank you.</span>
                </div>
                @else
                <div style="margin-top:0.75rem;">
                    <a href="{{ route('election.ballot') }}" class="btn-election">
                        <i class="fas fa-vote-yea mr-1"></i> Cast Your Vote
                    </a>
                </div>
                @endif
            @elseif(!$registered || !($oath && $oath->verified))
                <div class="election-status-row" style="margin-top:0.75rem;">
                    <span class="status-icon warn"><i class="fas fa-lock"></i></span>
                    <span style="color:#c0a0a0;">Complete your oath and registration above to unlock voting.</span>
                </div>
            @endif
        @elseif($election->phase === 'voting' && $election->voting_paused)
            <div class="election-status-row" style="margin-top:0.75rem;">
                <span class="status-icon warn"><i class="fas fa-pause-circle"></i></span>
                <span style="color:#c0a0a0;">Voting is temporarily paused. Check back shortly.</span>
            </div>
        @endif
    </div>

    {{-- Battalion stats — Commanders, FOs, and accepted candidates --}}
    @if($battalionStats)
    <div class="election-card">
        <h5><i class="fas fa-shield-alt mr-1"></i> Battalion Election Status</h5>

        <div class="election-stat-grid">
            <div class="election-stat-box">
                <div class="stat-number">{{ $battalionStats['oathed'] }}<span style="font-size:0.9rem;color:#c0a0a0;">/{{ $battalionStats['total'] }}</span></div>
                <div class="stat-label">Oathed</div>
            </div>
            <div class="election-stat-box">
                <div class="stat-number">{{ $battalionStats['registered'] }}<span style="font-size:0.9rem;color:#c0a0a0;">/{{ $battalionStats['total'] }}</span></div>
                <div class="stat-label">Registered</div>
            </div>
            @if($battalionStats['phase'] === 'voting')
            <div class="election-stat-box">
                <div class="stat-number">{{ $battalionStats['voted'] }}<span style="font-size:0.9rem;color:#c0a0a0;">/{{ $battalionStats['total'] }}</span></div>
                <div class="stat-label">Voted</div>
            </div>
            @endif
        </div>

        {{-- Named voter list --}}
        @if($battalionStats['show_names'] && $battalionStats['phase'] === 'voting' && $battalionStats['named_list'])
        <table class="election-voter-table">
            <thead>
                <tr>
                    <th>Knight</th>
                    <th>Registered</th>
                    <th>Voted</th>
                </tr>
            </thead>
            <tbody>
                @foreach($battalionStats['named_list'] as $member)
                <tr>
                    <td>
                        <a href="{{ route('profile', $member['rname']) }}" style="color:#efefef;">
                            {{ $member['kname'] }}
                        </a>
                    </td>
                    <td>
                        @if($member['registered'])
                            <span class="voted-yes"><i class="fas fa-check"></i></span>
                        @else
                            <span class="voted-no"><i class="fas fa-times"></i></span>
                        @endif
                    </td>
                    <td>
                        @if($member['voted'])
                            <span class="voted-yes"><i class="fas fa-check"></i></span>
                        @else
                            <span class="voted-no">&mdash;</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    @endif
    {{-- end @if($election) --}}

</div>