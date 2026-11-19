@extends('layouts.app')

@section('title', 'Candidates & Nominations')

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
.cand-row {
    background: #5a2424;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.85rem 1rem;
    margin-bottom: 0.75rem;
}
.pill {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
}
.nom-line {
    font-size: 0.82rem;
    color: #efefef;
    margin-top: 0.4rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.nom-line .label { color: #c0a0a0; min-width: 5.5rem; }
.nom-chip {
    background: #4a2020;
    border: 1px solid #8b3a3a;
    border-radius: 3px;
    padding: 0.1rem 0.45rem;
    font-size: 0.78rem;
}
.nom-chip form { display: inline; }
.nom-chip button {
    background: none;
    border: none;
    color: #c0a0a0;
    cursor: pointer;
    padding: 0 0 0 0.3rem;
}
.nom-chip button:hover { color: #efefef; }
.ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.88rem;
    width: 100%;
}
.ea-input:focus { outline: none; border-color: #efefef; }
.ea-label { font-size: 0.8rem; color: #c0a0a0; display: block; margin-bottom: 0.3rem; }
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
}
.btn-ea:hover { background-color: #a04040; color: #fff; text-decoration: none; }
.btn-ea.small { padding: 0.2rem 0.6rem; font-size: 0.78rem; }
.btn-ea.muted { background-color: #5a2424; border-color: #8b3a3a; }
.banner {
    background-color: #4a2020;
    border: 1px solid #f0ad4e;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    color: #f0ad4e;
    font-size: 0.88rem;
    margin-bottom: 1rem;
}
.note { font-size: 0.78rem; color: #c0a0a0; }
.form-grid { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-users mr-2"></i>Candidates &amp; Nominations — {{ $election->election_year }}
    </h4>
    <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">← Back to Dashboard</a>
</div>

@if($isAdminTest)
<div class="banner"><i class="fas fa-flask mr-1"></i> Admin test mode: view only.</div>
@elseif($isAssistant)
<div class="banner"><i class="fas fa-eye mr-1"></i> Assistant EA: view only.</div>
@elseif($phaseLocked)
<div class="banner">
    <i class="fas fa-lock mr-1"></i>
    Locked: candidates and nominations can't change once voting has opened. Anything unusual needs Council direction.
</div>
@endif

@if($canEdit && $election->debate_post_id)
<div class="banner" style="color:#efefef;">
    <i class="fas fa-info-circle mr-1"></i>
    The debate thread is already posted. Changes made here are not reflected in the candidates it mentions.
</div>
@endif

{{-- Candidate list --}}
<div class="ea-card">
    <h5>Candidates ({{ $candidates->where('status', 'accepted')->count() }} accepted)</h5>

    @forelse($candidates as $candidate)
    @php
        $k         = $candidate->knight;
        $nominator = $candidate->nominations->firstWhere('action', 'nominated');
        $seconds   = $candidate->nominations->where('action', 'seconded');
        $hasOath   = $k && in_array($k->pkey, $oathedPkeys);
        $colors    = [
            'accepted'  => ['#2d6a2d', '#5cb85c'],
            'nominated' => ['#4a3a1a', '#f0ad4e'],
            'declined'  => ['#4a2020', '#c0a0a0'],
            'withdrawn' => ['#4a2020', '#c0a0a0'],
        ];
        $c = $colors[$candidate->status] ?? ['#4a2020', '#c0a0a0'];
    @endphp
    <div class="cand-row">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
            <div>
                <strong>/u/{{ $k->rname ?? 'unknown' }}</strong>
                @if($hasOath)
                    <span class="note" style="color:#5cb85c;margin-left:0.5rem;"><i class="fas fa-check-circle"></i> {{ $oathYear }} oath</span>
                @else
                    <span class="note" style="color:#f0ad4e;margin-left:0.5rem;"><i class="fas fa-exclamation-circle"></i> no verified {{ $oathYear }} oath</span>
                @endif
            </div>
            <span class="pill" style="background:{{ $c[0] }};color:{{ $c[1] }};">{{ ucfirst($candidate->status) }}</span>
        </div>

        <div class="nom-line">
            <span class="label">Nominated by</span>
            @if($nominator)
                <span class="nom-chip">
                    /u/{{ $nominator->knight->rname ?? 'unknown' }}
                    @if($nominator->reddit_comment_url)
                        <a href="{{ $nominator->reddit_comment_url }}" target="_blank" style="color:#c0a0a0;"><i class="fas fa-external-link-alt"></i></a>
                    @endif
                    @if($canEdit)
                    <form method="POST" action="{{ route('election.nominations.remove', $nominator->pkey) }}">
                        @csrf
                        <button type="submit" title="Remove this record" onclick="return confirm('Remove this nomination record?')">&times;</button>
                    </form>
                    @endif
                </span>
            @else
                <span class="note">— none recorded</span>
            @endif
        </div>

        <div class="nom-line">
            <span class="label">Seconded by ({{ $seconds->count() }})</span>
            @forelse($seconds as $second)
                <span class="nom-chip">
                    /u/{{ $second->knight->rname ?? 'unknown' }}
                    @if($second->reddit_comment_url)
                        <a href="{{ $second->reddit_comment_url }}" target="_blank" style="color:#c0a0a0;"><i class="fas fa-external-link-alt"></i></a>
                    @endif
                    @if($canEdit)
                    <form method="POST" action="{{ route('election.nominations.remove', $second->pkey) }}">
                        @csrf
                        <button type="submit" title="Remove this record" onclick="return confirm('Remove this seconding record?')">&times;</button>
                    </form>
                    @endif
                </span>
            @empty
                <span class="note">— none recorded</span>
            @endforelse
        </div>

        @if($canEdit)
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;margin-top:0.75rem;">
            <form method="POST" action="{{ route('election.candidates.update', $candidate->pkey) }}" style="display:flex;gap:0.4rem;align-items:center;">
                @csrf
                <select name="status" class="ea-input" style="width:auto;padding:0.2rem 0.4rem;font-size:0.8rem;">
                    @foreach(['nominated', 'accepted', 'declined', 'withdrawn'] as $s)
                    <option value="{{ $s }}" {{ $candidate->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-ea small">Set status</button>
            </form>
            <form method="POST" action="{{ route('election.candidates.remove', $candidate->pkey) }}">
                @csrf
                <button type="submit" class="btn-ea small muted"
                    onclick="return confirm('Remove /u/{{ $k->rname ?? 'this candidate' }} from the candidate list?')">
                    <i class="fas fa-times mr-1"></i> Remove
                </button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <p class="note">No candidates added yet.</p>
    @endforelse
</div>

@if($canEdit)
{{-- Add candidate --}}
<div class="ea-card">
    <h5>Add a candidate</h5>
    <form method="POST" action="{{ route('election.candidates.store') }}">
        @csrf
        <div class="form-grid">
            <div style="flex:1;min-width:200px;">
                <label class="ea-label">Knight</label>
                <select name="fkeyknight" class="ea-input" required>
                    <option value="">— Select knight —</option>
                    @foreach($candidateKnights as $k)
                    <option value="{{ $k->pkey }}">/u/{{ $k->rname }}{{ in_array($k->pkey, $oathedPkeys) ? '' : '  (no verified oath)' }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px;">
                <label class="ea-label">Status</label>
                <select name="status" class="ea-input">
                    <option value="nominated">Nominated</option>
                    <option value="accepted">Accepted</option>
                </select>
            </div>
            <div style="flex:1;min-width:200px;">
                <label class="ea-label">Nomination comment link (optional)</label>
                <input type="url" name="nomination_url" class="ea-input" placeholder="https://www.reddit.com/r/AprilKnights/comments/...">
            </div>
            <div>
                <button type="submit" class="btn-ea"><i class="fas fa-plus mr-1"></i> Add</button>
            </div>
        </div>
        <div class="note" style="margin-top:0.5rem;">
            A knight needs a verified {{ $oathYear }} oath before they can be set to Accepted. The EA and Assistant EA can't be candidates.
        </div>
    </form>
</div>

{{-- Record nomination / seconding --}}
<div class="ea-card">
    <h5>Record a nomination or second</h5>
    <form method="POST" action="{{ route('election.nominations.store') }}">
        @csrf
        <div class="form-grid">
            <div style="flex:1;min-width:180px;">
                <label class="ea-label">Candidate</label>
                <select name="fkeycandidate" class="ea-input" required>
                    <option value="">— Select candidate —</option>
                    @foreach($candidates as $c)
                    <option value="{{ $c->pkey }}">/u/{{ $c->knight->rname ?? 'unknown' }} ({{ $c->status }})</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:180px;">
                <label class="ea-label">Knight who acted</label>
                <select name="fkeyknight" class="ea-input" required>
                    <option value="">— Select knight —</option>
                    @foreach($knights as $k)
                    <option value="{{ $k->pkey }}">/u/{{ $k->rname }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="ea-label">Action</label>
                <select name="action" class="ea-input">
                    <option value="seconded">Seconded</option>
                    <option value="nominated">Nominated</option>
                </select>
            </div>
            <div style="flex:1;min-width:200px;">
                <label class="ea-label">Reddit comment link (optional)</label>
                <input type="url" name="reddit_comment_url" class="ea-input" placeholder="https://www.reddit.com/r/AprilKnights/comments/...">
            </div>
            <div>
                <button type="submit" class="btn-ea"><i class="fas fa-plus mr-1"></i> Record</button>
            </div>
        </div>
        <div class="note" style="margin-top:0.5rem;">
            Each candidate has one nominator; everyone else who supports them is recorded as a second. Nominations themselves still happen on Reddit; this is the clerical record.
        </div>
    </form>
</div>
@endif
@endsection