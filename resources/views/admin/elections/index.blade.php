@extends('layouts.app')

@section('title', 'Elections')

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
.election-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.election-table th {
    color: #c0a0a0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.election-table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.election-table tr:last-child td {
    border-bottom: none;
}
.phase-pill {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
    background-color: #8b3a3a;
    color: #efefef;
}
.phase-pill.complete {
    background-color: #2d6a2d;
    color: #5cb85c;
}
.phase-pill.voting {
    background-color: #4a3a1a;
    color: #f0ad4e;
}
.btn-admin {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.35rem 0.85rem;
    border-radius: 4px;
    font-size: 0.82rem;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.15s ease;
}
.btn-admin:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
</style>

@push('styles')
<style>
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
</style>
@endpush

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Elections</li>
    </ol>
</nav>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
    <h2 style="margin:0;">Elections</h2>
    <a href="{{ route('admin.elections.create') }}" class="btn-admin">
        <i class="fas fa-plus mr-1"></i> New Election
    </a>
</div>

<div class="admin-card">
    @if($elections->isEmpty())
    <p style="color:#c0a0a0;font-size:0.88rem;margin:0;">No elections found.</p>
    @else
    <table class="election-table">
        <thead>
            <tr>
                <th>Year</th>
                <th>Phase</th>
                <th>Voting Paused</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($elections as $election)
            <tr>
                <td><strong>{{ $election->election_year }}</strong></td>
                <td>
                    <span class="phase-pill {{ $election->phase === 'complete' ? 'complete' : ($election->phase === 'voting' ? 'voting' : '') }}">
                        {{ ucfirst($election->phase) }}
                    </span>
                </td>
                <td>
                    @if($election->phase === 'voting')
                        @if($election->voting_paused)
                            <span style="color:#f0ad4e;font-size:0.82rem;"><i class="fas fa-pause-circle mr-1"></i>Paused</span>
                        @else
                            <span style="color:#5cb85c;font-size:0.82rem;"><i class="fas fa-play-circle mr-1"></i>Open</span>
                        @endif
                    @else
                        <span style="color:#c0a0a0;font-size:0.82rem;">—</span>
                    @endif
                </td>
                <td style="color:#c0a0a0;font-size:0.82rem;">{{ $election->crtsetdt->format('M j, Y') }}</td>
                <td>
                    <a href="{{ route('admin.elections.show', $election->pkey) }}" class="btn-admin">
                        View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection