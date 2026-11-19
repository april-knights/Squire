@extends('layouts.app')

@section('title', 'Audit Results')

@section('content')
<style>
.audit-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.audit-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.audit-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.audit-table th {
    color: #c0a0a0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.audit-table td {
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #4a2020;
    color: #f0ad4e;
}
.audit-table tr:last-child td { border-bottom: none; }
.ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    width: 100%;
}
.ea-input:focus { outline: none; border-color: #efefef; }
.audit-card label { font-size: 0.8rem; color: #c0a0a0; display: block; margin-bottom: 0.3rem; }
.btn-ea {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.4rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
}
.btn-ea:hover { background-color: #a04040; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-clipboard-check mr-2"></i>Audit Results — {{ $election->election_year }}
    </h4>
    <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Dashboard
    </a>
</div>

@if(empty($discrepancies))
<div style="background:#2d6a2d;border:1px solid #5cb85c;border-radius:6px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;color:#efefef;text-align:center;">
    <i class="fas fa-check-circle" style="font-size:2rem;color:#5cb85c;margin-bottom:0.5rem;display:block;"></i>
    <h4 style="color:#5cb85c;margin-bottom:0.25rem;">Audit Passed</h4>
    <p style="color:#efefef;margin:0;">
        All {{ $checkedCount ?? 0 }} audit record(s) match the database ballots, and every ballot in the database has a matching audit record.
    </p>
</div>
@else
<div style="background:#4a1f1f;border:1px solid #c0392b;border-radius:6px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;color:#efefef;">
    <i class="fas fa-exclamation-triangle" style="color:#c0392b;margin-right:0.5rem;"></i>
    <strong>{{ count($discrepancies) }} discrepanc{{ count($discrepancies) === 1 ? 'y' : 'ies' }} found</strong>
    <span style="color:#c0a0a0;font-size:0.85rem;margin-left:0.5rem;">
        out of {{ $checkedCount ?? 0 }} audit record(s) checked.
    </span>
    <div style="font-size:0.82rem;color:#c0a0a0;margin-top:0.5rem;">
        A discrepancy is not proof of tampering. Common causes: a DM missing from what you pasted, an edited or truncated DM,
        or a vote counted twice. Resolve each one before announcing a result; notify an Admin to review the database change logs for anything unexplained.
    </div>
</div>

<div class="audit-card">
    <h5>Discrepancies</h5>
    <table class="audit-table">
        <thead>
            <tr>
                <th>Knight ID</th>
                <th>Issue</th>
                <th>Audit DM</th>
                <th>Database</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discrepancies as $disc)
            <tr>
                <td>{{ $disc['knight_pkey'] }}</td>
                <td>{{ $disc['issue'] }}</td>
                <td style="font-family:monospace;">{{ $disc['csv'] ?? '—' }}</td>
                <td style="font-family:monospace;">{{ $disc['db'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="audit-card">
    <h5>Run the audit again</h5>
    <form method="POST" action="{{ route('election.audit') }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom:0.75rem;">
            <label>Election Passphrase</label>
            <input type="password" name="passphrase" class="ea-input" required autocomplete="off">
        </div>
        <div style="margin-bottom:0.75rem;">
            <label>Paste audit DMs</label>
            <textarea name="audit_text" class="ea-input" style="min-height:110px;resize:vertical;"
                placeholder="Knight ID: 42&#10;Rankings: 15,12,18"></textarea>
        </div>
        <div style="margin-bottom:0.75rem;">
            <label>…or upload a file (.csv / .txt)</label>
            <input type="file" name="audit_csv" accept=".csv,.txt" style="color:#efefef;font-size:0.85rem;">
        </div>
        <button type="submit" class="btn-ea">
            <i class="fas fa-search mr-1"></i> Run Audit
        </button>
    </form>
</div>
@endsection