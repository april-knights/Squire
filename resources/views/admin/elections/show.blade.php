@extends('layouts.app')

@section('title', 'Election ' . $election->election_year)

@section('content')
<style>
.admin-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.admin-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.form-label-ea {
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
    font-size: 0.88rem;
    width: 100%;
}
.ea-input:focus {
    outline: none;
    border-color: #efefef;
}
.ea-select {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.88rem;
    width: 100%;
}
.btn-admin {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.35rem 0.85rem;
    border-radius: 4px;
    font-size: 0.82rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.15s ease;
    margin-right: 0.4rem;
    margin-bottom: 0.4rem;
}
.btn-admin:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
.btn-admin.muted {
    background-color: #5a2424;
    border-color: #8b3a3a;
}
.btn-admin.muted:hover {
    background-color: #6b2b2b;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.data-table th {
    color: #c0a0a0;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.data-table td {
    padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
    vertical-align: middle;
}
.data-table tr:last-child td {
    border-bottom: none;
}
.status-pill {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
}
.phase-log-row {
    display: flex;
    gap: 1rem;
    font-size: 0.82rem;
    padding: 0.4rem 0;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.phase-log-row:last-child { border-bottom: none; }
.phase-log-time {
    color: #c0a0a0;
    white-space: nowrap;
    flex-shrink: 0;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-vote-yea mr-2"></i>Election {{ $election->election_year }}
        <span class="status-pill" style="background:#8b3a3a;color:#efefef;font-size:0.7rem;vertical-align:middle;margin-left:0.5rem;">
            {{ ucfirst($election->phase) }}
        </span>
    </h4>
    <div>
        <a href="{{ route('election.dashboard') }}" class="btn-admin">
            <i class="fas fa-tachometer-alt mr-1"></i> EA Dashboard
        </a>
        <a href="{{ route('admin.elections.index') }}" style="color:#c0a0a0;font-size:0.85rem;margin-left:0.5rem;">
            ← Back to Elections
        </a>
    </div>
</div>

<div class="row">
<div class="col-lg-7">

    {{-- Election Administrator --}}
    <div class="admin-card">
        <h5><i class="fas fa-crown mr-1"></i> Election Administrator</h5>

        @if($administrator)
        <div style="background:#5a2424;border:1px solid #8b3a3a;border-radius:4px;padding:0.75rem 1rem;margin-bottom:0.75rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
            <div>
                <strong>{{ $administrator->knight->kname }}</strong>
                <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.5rem;">/u/{{ $administrator->knight->rname }}</span>
                <span class="status-pill" style="background:#2d6a2d;color:#5cb85c;margin-left:0.5rem;">Full EA</span>
            </div>
            <form method="POST" action="{{ route('admin.elections.remove-admin', $election->pkey) }}">
                @csrf
                <input type="hidden" name="ea_pkey" value="{{ $administrator->pkey }}">
                <button type="submit" class="btn-admin muted"
                    onclick="return confirm('Remove {{ $administrator->knight->kname }} as Election Administrator?')">
                    Remove
                </button>
            </form>
        </div>
        @else
        <p style="color:#c0a0a0;font-size:0.85rem;">No Election Administrator appointed yet.</p>
        @endif

        @if($assistant)
        <div style="background:#5a2424;border:1px solid #8b3a3a;border-radius:4px;padding:0.75rem 1rem;margin-bottom:0.75rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
            <div>
                <strong>{{ $assistant->knight->kname }}</strong>
                <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.5rem;">/u/{{ $assistant->knight->rname }}</span>
                <span class="status-pill" style="background:#4a3a1a;color:#f0ad4e;margin-left:0.5rem;">Assistant EA</span>
            </div>
            <form method="POST" action="{{ route('admin.elections.remove-admin', $election->pkey) }}">
                @csrf
                <input type="hidden" name="ea_pkey" value="{{ $assistant->pkey }}">
                <button type="submit" class="btn-admin muted"
                    onclick="return confirm('Remove {{ $assistant->knight->kname }} as Assistant EA?')">
                    Remove
                </button>
            </form>
        </div>
        @endif

        {{-- Appoint form --}}
        @if(!$administrator || !$assistant)
        <form method="POST" action="{{ route('admin.elections.appoint', $election->pkey) }}" style="margin-top:0.75rem;">
            @csrf
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label class="form-label-ea">Knight</label>
                    <select name="fkeyknight" class="ea-select" required>
                        <option value="">— Select Knight —</option>
                        @foreach($eligibleKnights as $k)
                        <option value="{{ $k->pkey }}">{{ $k->kname }} (/u/{{ $k->rname }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:160px;">
                    <label class="form-label-ea">Role</label>
                    <select name="is_assistant" class="ea-select">
                        @if(!$administrator)
                        <option value="0">Full EA</option>
                        @endif
                        @if(!$assistant)
                        <option value="1">Assistant EA</option>
                        @endif
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-admin" style="margin-bottom:0;">
                        <i class="fas fa-user-plus mr-1"></i> Appoint
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

    {{-- Candidates --}}
    <div class="admin-card">
        <h5><i class="fas fa-users mr-1"></i> Candidates</h5>

        @if($candidates->isEmpty())
        <p style="color:#c0a0a0;font-size:0.85rem;">No candidates added yet.</p>
        @else
        <table class="data-table" style="margin-bottom:1rem;">
            <thead>
                <tr>
                    <th>Knight</th>
                    <th>Status</th>
                    <th>Nominations</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidates as $candidate)
                <tr>
                    <td>
                        <div>{{ $candidate->knight->kname }}</div>
                        <div style="color:#c0a0a0;font-size:0.78rem;">/u/{{ $candidate->knight->rname }}</div>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'accepted'  => ['bg'=>'#2d6a2d','color'=>'#5cb85c'],
                                'nominated' => ['bg'=>'#4a3a1a','color'=>'#f0ad4e'],
                                'declined'  => ['bg'=>'#5a2424','color'=>'#c0a0a0'],
                                'withdrawn' => ['bg'=>'#5a2424','color'=>'#c0a0a0'],
                            ];
                            $sc = $statusColors[$candidate->status] ?? ['bg'=>'#5a2424','color'=>'#c0a0a0'];
                        @endphp
                        <span class="status-pill" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                            {{ ucfirst($candidate->status) }}
                        </span>
                    </td>
                    <td style="font-size:0.78rem;color:#c0a0a0;">
                        {{ $candidate->nominations->where('action','nominated')->count() }} nom
                        / {{ $candidate->nominations->where('action','seconded')->count() }} sec
                    </td>
                    <td>
                        <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                            {{-- Quick status update --}}
                            <form method="POST" action="{{ route('admin.elections.candidates.update', [$election->pkey, $candidate->pkey]) }}">
                                @csrf
                                <input type="hidden" name="nomination_url" value="{{ $candidate->nomination_url }}">
                                <select name="status" class="ea-select" style="width:auto;padding:0.2rem 0.4rem;font-size:0.75rem;"
                                    onchange="this.form.submit()">
                                    @foreach(['nominated','accepted','declined','withdrawn'] as $s)
                                    <option value="{{ $s }}" {{ $candidate->status === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('admin.elections.candidates.remove', [$election->pkey, $candidate->pkey]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-admin muted"
                                    style="padding:0.2rem 0.5rem;font-size:0.75rem;margin:0;"
                                    onclick="return confirm('Remove {{ $candidate->knight->kname }} from candidates?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Add candidate --}}
        <form method="POST" action="{{ route('admin.elections.candidates.add', $election->pkey) }}">
            @csrf
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label class="form-label-ea">Knight</label>
                    <select name="fkeyknight" class="ea-select" required>
                        <option value="">— Select Knight —</option>
                        @foreach($eligibleKnights as $k)
                        <option value="{{ $k->pkey }}">{{ $k->kname }} (/u/{{ $k->rname }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:120px;">
                    <label class="form-label-ea">Status</label>
                    <select name="status" class="ea-select">
                        <option value="nominated">Nominated</option>
                        <option value="accepted">Accepted</option>
                        <option value="declined">Declined</option>
                        <option value="withdrawn">Withdrawn</option>
                    </select>
                </div>
                <div style="flex:1;min-width:180px;">
                    <label class="form-label-ea">Nomination URL (optional)</label>
                    <input type="url" name="nomination_url" class="ea-input"
                        placeholder="https://reddit.com/...">
                </div>
                <div>
                    <button type="submit" class="btn-admin" style="margin-bottom:0;">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Nomination tracking --}}
    <div class="admin-card">
        <h5><i class="fas fa-hand-point-up mr-1"></i> Nomination & Seconding Records</h5>

        @php
            $allNominations = $candidates->flatMap(fn($c) => $c->nominations)->sortByDesc('crtsetdt');
        @endphp

        @if($allNominations->isEmpty())
        <p style="color:#c0a0a0;font-size:0.85rem;">No nomination records yet.</p>
        @else
        <table class="data-table" style="margin-bottom:1rem;">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Action</th>
                    <th>By</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allNominations as $nom)
                <tr>
                    <td style="font-size:0.82rem;">{{ $nom->candidate->knight->kname }}</td>
                    <td>
                        <span class="status-pill"
                            style="background:{{ $nom->action === 'nominated' ? '#2d6a2d' : '#4a3a1a' }};
                                   color:{{ $nom->action === 'nominated' ? '#5cb85c' : '#f0ad4e' }};">
                            {{ ucfirst($nom->action) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;">{{ $nom->knight->kname }}</td>
                    <td>
                        @if($nom->reddit_comment_url)
                        <a href="{{ $nom->reddit_comment_url }}" target="_blank"
                            style="color:#c0a0a0;font-size:0.78rem;">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        @else
                        <span style="color:#4a2020;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Add nomination --}}
        @if($candidates->where('status','nominated')->count() + $candidates->where('status','accepted')->count() > 0)
        <form method="POST" action="{{ route('admin.elections.nominations.add', $election->pkey) }}">
            @csrf
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:160px;">
                    <label class="form-label-ea">Candidate</label>
                    <select name="fkeycandidate" class="ea-select" required>
                        <option value="">— Select Candidate —</option>
                        @foreach($candidates->whereIn('status',['nominated','accepted']) as $c)
                        <option value="{{ $c->pkey }}">{{ $c->knight->kname }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="form-label-ea">Knight (who acted)</label>
                    <select name="fkeyknight" class="ea-select" required>
                        <option value="">— Select Knight —</option>
                        @foreach($eligibleKnights as $k)
                        <option value="{{ $k->pkey }}">{{ $k->kname }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="min-width:120px;">
                    <label class="form-label-ea">Action</label>
                    <select name="action" class="ea-select">
                        <option value="seconded">Seconded</option>
                        <option value="nominated">Nominated</option>
                    </select>
                </div>
                <div style="flex:1;min-width:180px;">
                    <label class="form-label-ea">Comment URL (optional)</label>
                    <input type="url" name="reddit_comment_url" class="ea-input"
                        placeholder="https://reddit.com/...">
                </div>
                <div>
                    <button type="submit" class="btn-admin" style="margin-bottom:0;">
                        <i class="fas fa-plus mr-1"></i> Record
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

</div>
<div class="col-lg-5">

    {{-- Election stats --}}
    <div class="admin-card">
        <h5><i class="fas fa-chart-bar mr-1"></i> At a Glance</h5>
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
            <div>
                <div style="font-size:1.5rem;font-weight:bold;color:#f0ad4e;">{{ $candidates->where('status','accepted')->count() }}</div>
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#c0a0a0;">Accepted Candidates</div>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:bold;color:#f0ad4e;">{{ $registrations->count() }}</div>
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#c0a0a0;">Registered Voters</div>
            </div>
            <div>
                <div style="font-size:1.5rem;font-weight:bold;color:#f0ad4e;">{{ $voteCount }}</div>
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;color:#c0a0a0;">Votes Cast</div>
            </div>
        </div>
    </div>

    {{-- Admin test mode --}}
    <div class="admin-card">
        <h5><i class="fas fa-flask mr-1"></i> Admin Test Mode</h5>
        <p style="color:#c0a0a0;font-size:0.82rem;margin-bottom:0.75rem;">
            When enabled, Admins can access the full EA dashboard for testing.
            Resets automatically when election completes.
        </p>
        <form method="POST" action="{{ route('admin.elections.toggle-test-mode', $election->pkey) }}">
            @csrf
            @include('partials.toggle', [
                'name'    => 'admin_test_mode',
                'checked' => $election->admin_test_mode,
                'label'   => 'Admin test mode',
            ])
            <button type="submit" class="btn-admin" style="margin-top:0.75rem;">Save</button>
        </form>
    </div>

    {{-- Notes --}}
    <div class="admin-card">
        <h5><i class="fas fa-sticky-note mr-1"></i> Notes</h5>
        <form method="POST" action="{{ route('admin.elections.update', $election->pkey) }}">
            @csrf
            <textarea name="notes" class="ea-input"
                style="min-height:100px;resize:vertical;margin-bottom:0.75rem;">{{ $election->notes }}</textarea>
            <button type="submit" class="btn-admin">
                <i class="fas fa-save mr-1"></i> Save Notes
            </button>
        </form>
    </div>

    {{-- Reddit thread URLs --}}
    <div class="admin-card">
        <h5><i class="fas fa-reddit mr-1"></i> Thread Links</h5>
        @if($election->nomination_thread_url)
        <div style="margin-bottom:0.5rem;">
            <a href="{{ $election->nomination_thread_url }}" target="_blank"
                style="color:#c0a0a0;font-size:0.85rem;">
                <i class="fas fa-external-link-alt mr-1"></i> Nomination Thread
            </a>
        </div>
        @endif
        @if($election->debate_thread_url)
        <div style="margin-bottom:0.5rem;">
            <a href="{{ $election->debate_thread_url }}" target="_blank"
                style="color:#c0a0a0;font-size:0.85rem;">
                <i class="fas fa-external-link-alt mr-1"></i> Debate Thread
            </a>
        </div>
        @endif
        @if($election->registration_thread_url)
        <div>
            <a href="{{ $election->registration_thread_url }}" target="_blank"
                style="color:#c0a0a0;font-size:0.85rem;">
                <i class="fas fa-external-link-alt mr-1"></i> Registration Thread
            </a>
        </div>
        @endif
        @if(!$election->nomination_thread_url && !$election->debate_thread_url && !$election->registration_thread_url)
        <p style="color:#c0a0a0;font-size:0.85rem;margin:0;">No threads posted yet.</p>
        @endif
    </div>

    {{-- Phase log --}}
    <div class="admin-card">
        <h5><i class="fas fa-history mr-1"></i> Phase Log</h5>
        @forelse($phaseLog as $log)
        <div class="phase-log-row">
            <span class="phase-log-time">{{ $log->crtsetdt->format('M j g:ia') }}</span>
            <span>
                @if($log->from_phase)
                    {{ ucfirst($log->from_phase) }} → {{ ucfirst($log->to_phase) }}
                @else
                    Created
                @endif
                @if($log->note)
                    <span style="color:#c0a0a0;"> — {{ $log->note }}</span>
                @endif
            </span>
        </div>
        @empty
        <p style="color:#c0a0a0;font-size:0.85rem;margin:0;">No transitions yet.</p>
        @endforelse
    </div>

</div>
</div>
@endsection