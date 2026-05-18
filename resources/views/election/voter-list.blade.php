@extends('layouts.app')

@section('title', 'Voter List')

@section('content')
<style>
.voter-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.voter-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.voter-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.voter-table th {
    color: #c0a0a0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.voter-table td {
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.voter-table tr:last-child td {
    border-bottom: none;
}
.voted-yes { color: #5cb85c; }
.voted-no  { color: #c0a0a0; }
.search-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.88rem;
    width: 100%;
    margin-bottom: 1rem;
}
.search-input:focus {
    outline: none;
    border-color: #efefef;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-list mr-2"></i>Voter List — {{ $election->election_year }}
    </h4>
    <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Dashboard
    </a>
</div>

<div class="voter-card">
    <h5>Summary</h5>
    <div style="display:flex;gap:2rem;flex-wrap:wrap;">
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#f0ad4e;">{{ $registrations->count() }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Registered</span>
        </div>
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#5cb85c;">{{ count($votedPkeys) }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Voted</span>
        </div>
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#c0a0a0;">{{ $registrations->count() - count($votedPkeys) }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Not Yet Voted</span>
        </div>
    </div>
</div>

<div class="voter-card">
    <h5>Registered Voters</h5>
    <input
        type="text"
        class="search-input"
        id="voter-search"
        placeholder="Search by name or username..."
    >
    <table class="voter-table" id="voter-table">
        <thead>
            <tr>
                <th>Knight</th>
                <th>Reddit</th>
                <th>Registered</th>
                <th>Voted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $reg)
            <tr class="voter-row">
                <td>{{ $reg->knight->rname }}</td>
                <td style="color:#c0a0a0;">/u/{{ $reg->knight->rname }}</td>
                <td style="color:#c0a0a0;font-size:0.8rem;">{{ $reg->registered_at->format('M j, Y') }}</td>
                <td>
                    @if(in_array($reg->fkeyknight, $votedPkeys))
                        <span class="voted-yes"><i class="fas fa-check"></i> Yes</span>
                    @else
                        <span class="voted-no">— No</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
(function () {
    var input = document.getElementById('voter-search');
    var rows  = document.querySelectorAll('.voter-row');

    input.addEventListener('keyup', function () {
        var term = this.value.toLowerCase();
        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
})();
</script>
@endsection