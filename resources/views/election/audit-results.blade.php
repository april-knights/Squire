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
    color: #efefef;
}
.audit-table tr:last-child td {
    border-bottom: none;
}
.discrepancy-row td {
    color: #f0ad4e;
}
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
    <p style="color:#c0a0a0;margin:0;">All records in the uploaded CSV match the database exactly.</p>
</div>
@else
<div style="background:#4a1f1f;border:1px solid #c0392b;border-radius:6px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;color:#efefef;">
    <i class="fas fa-exclamation-triangle" style="color:#c0392b;margin-right:0.5rem;"></i>
    <strong>{{ count($discrepancies) }} discrepancy{{ count($discrepancies) === 1 ? '' : 'ies' }} found.</strong>
    <span style="color:#c0a0a0;font-size:0.85rem;margin-left:0.5rem;">
        Review each entry below and compare against database logs.
    </span>
</div>

<div class="audit-card">
    <h5>Discrepancies</h5>
    <table class="audit-table">
        <thead>
            <tr>
                <th>Knight Pkey</th>
                <th>Issue</th>
                <th>CSV Value</th>
                <th>DB Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discrepancies as $disc)
            <tr class="discrepancy-row">
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
    <h5>Upload Another CSV</h5>
    <form method="POST" action="{{ route('election.audit') }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom:0.75rem;">
            <input type="file" name="audit_csv" accept=".csv,.txt"
                style="color:#efefef;font-size:0.88rem;">
        </div>
        <button type="submit" class="btn btn-sm"
            style="background:#8b3a3a;border-color:#a04040;color:#efefef;">
            <i class="fas fa-upload mr-1"></i> Run Audit
        </button>
    </form>
</div>
@endsection