@extends('layouts.app')

@section('title', 'Oaths')

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
.oath-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.oath-table th {
    color: #c0a0a0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid #8b3a3a;
    text-align: left;
}
.oath-table td {
    padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #4a2020;
    color: #efefef;
}
.oath-table tr:last-child td {
    border-bottom: none;
}
.verified-yes { color: #5cb85c; }
.verified-no  { color: #f0ad4e; }
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
.btn-sm-admin {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.2rem 0.6rem;
    border-radius: 3px;
    font-size: 0.75rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.15s ease;
}
.btn-sm-admin:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
.btn-sm-admin.muted {
    background-color: #5a2424;
    border-color: #8b3a3a;
}
.btn-sm-admin.muted:hover {
    background-color: #6b2b2b;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;">
        <i class="fas fa-scroll mr-2"></i>Oaths — {{ $oathYear }}
    </h4>
    @if($oathThreadUrl)
    <a href="{{ $oathThreadUrl }}" target="_blank" style="color:#c0a0a0;font-size:0.85rem;">
        <i class="fas fa-external-link-alt mr-1"></i> View Oath Thread
    </a>
    @endif
</div>

<div class="admin-card">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;margin-bottom:1rem;">
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#5cb85c;">{{ $oaths->where('verified', true)->count() }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Verified</span>
        </div>
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#f0ad4e;">{{ $oaths->where('verified', false)->count() }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Unverified</span>
        </div>
        <div>
            <span style="font-size:1.4rem;font-weight:bold;color:#efefef;">{{ $oaths->count() }}</span>
            <span style="color:#c0a0a0;font-size:0.8rem;margin-left:0.4rem;">Total</span>
        </div>
    </div>

    <input type="text" class="search-input" id="oath-search" placeholder="Search by name or username...">

    <table class="oath-table" id="oath-table">
        <thead>
            <tr>
                <th>Knight</th>
                <th>Reddit</th>
                <th>Submitted</th>
                <th>Verified</th>
                <th>Comment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($oaths as $oath)
            <tr class="oath-row">
                <td>{{ $oath->knight->kname }}</td>
                <td style="color:#c0a0a0;">/u/{{ $oath->knight->rname }}</td>
                <td style="color:#c0a0a0;font-size:0.8rem;">{{ $oath->crtsetdt->format('M j, Y') }}</td>
                <td>
                    @if($oath->verified)
                        <span class="verified-yes"><i class="fas fa-check-circle mr-1"></i>Yes</span>
                    @else
                        <span class="verified-no"><i class="fas fa-exclamation-circle mr-1"></i>No</span>
                    @endif
                </td>
                <td>
                    @if($oath->comment_url)
                    <a href="{{ $oath->comment_url }}" target="_blank"
                        style="color:#c0a0a0;font-size:0.78rem;">
                        <i class="fas fa-external-link-alt mr-1"></i>View
                    </a>
                    @else
                    <span style="color:#4a2020;">—</span>
                    @endif
                </td>
                <td>
                    @if($oath->verified)
                    <form method="POST" action="{{ route('admin.oaths.unverify', $oath->pkey) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm-admin muted"
                            onclick="return confirm('Remove verification from {{ $oath->knight->kname }}?')">
                            Unverify
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.oaths.verify', $oath->pkey) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm-admin">
                            Verify
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
(function () {
    var input = document.getElementById('oath-search');
    var rows  = document.querySelectorAll('.oath-row');

    input.addEventListener('keyup', function () {
        var term = this.value.toLowerCase();
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
})();
</script>
@endsection