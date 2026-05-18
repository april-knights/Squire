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
        <li class="breadcrumb-item active">Oaths — {{ $oathYear }}</li>
    </ol>
</nav>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
    <h2 style="margin:0;">Oaths — {{ $oathYear }}</h2>
    @if($oathThreadUrl)
    <a href="{{ $oathThreadUrl }}" target="_blank" style="color:#c0a0a0;font-size:0.85rem;">
        <i class="fas fa-external-link-alt mr-1"></i> View Oath Thread
    </a>
    @endif
</div>

<div class="admin-card">

    {{-- Batch verify --}}
    <form method="POST" action="{{ route('admin.oaths.batch-verify') }}" style="margin-bottom:1rem;">
        @csrf
        <button type="submit" class="btn-sm-admin"
            onclick="return confirm('Scan the oath thread and verify all matching knights?')">
            <i class="fas fa-sync mr-1"></i> Run Batch Verification
        </button>
    </form>

    @if(session('batch_results'))
    @php $br = session('batch_results'); @endphp
    <div style="background:#3a1a1a;border:1px solid #8b3a3a;border-radius:4px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;">
        <strong>Batch Results:</strong>
        <span style="color:#5cb85c;margin-left:0.75rem;"><i class="fas fa-check mr-1"></i>{{ $br['verified'] }} newly verified</span>
        <span style="color:#c0a0a0;margin-left:0.75rem;"><i class="fas fa-check-double mr-1"></i>{{ $br['alreadyOk'] }} already verified</span>
        <span style="color:#f0ad4e;margin-left:0.75rem;"><i class="fas fa-times mr-1"></i>{{ $br['notFound'] }} not found on thread</span>
        @if(!empty($br['noSquire']))
        <div style="margin-top:0.5rem;color:#f0ad4e;">
            <i class="fas fa-user-times mr-1"></i> {{ count($br['noSquire']) }} commenter(s) not in Squire:
            <span style="color:#c0a0a0;">{{ implode(', ', array_map(fn($r) => '/u/' . $r, $br['noSquire'])) }}</span>
        </div>
        @endif
    </div>
    @endif

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
                <td>{{ $oath->knight->rname }}</td>
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
                            onclick="return confirm('Remove verification from {{ $oath->knight->rname }}?')">
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