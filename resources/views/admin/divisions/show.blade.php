@extends('layouts.app')
@section('title', 'Admin — ' . $division->name)
@section('full_width', true)

@push('styles')
<style>
.card { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.card-header { background-color: rgba(0,0,0,0.3); border-bottom: 1px solid #8b3a3a; color: #efefef; font-weight: 600; }
.card-body { color: #efefef; }
dt { color: #c9a0a0; }
dd { color: #efefef; }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.div-color-swatch { display: inline-block; width: 1.25rem; height: 1.25rem; border-radius: 3px; border: 1px solid rgba(255,255,255,0.2); vertical-align: middle; margin-right: 0.4rem; }
/* Member table */
.member-table { width: 100%; }
.member-table th { color: #c9a0a0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #8b3a3a; padding: 0.4rem 0.5rem; }
.member-table td { padding: 0.4rem 0.5rem; border-bottom: 1px solid rgba(139,58,58,0.3); color: #efefef; }
/* Knight search */
.knight-search-wrapper { position: relative; }
#knightSearchResults {
    position: absolute;
    z-index: 100;
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.knight-result-item {
    padding: 0.4rem 0.75rem;
    cursor: pointer;
    color: #efefef;
    font-size: 0.875rem;
}
.knight-result-item:hover { background-color: rgba(139,58,58,0.4); }
.form-control {
    background-color: rgba(0,0,0,0.3);
    border: 1px solid #8b3a3a;
    color: #efefef;
}
.form-control:focus {
    background-color: rgba(0,0,0,0.4);
    border-color: #c9a0a0;
    color: #efefef;
    box-shadow: 0 0 0 0.2rem rgba(139,58,58,0.4);
}
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/divisions">Divisions</a></li>
        <li class="breadcrumb-item active">{{ $division->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $division->name }}
        @if($division->color)
            <span class="div-color-swatch" style="background-color: {{ $division->color }};"></span>
        @endif
        @if($division->delflg)
            <span class="badge badge-danger">Deleted</span>
        @elseif(!$division->activeflg)
            <span class="badge badge-warning">Inactive</span>
        @else
            <span class="badge badge-success">Active</span>
        @endif
    </h2>
    <div>
        <a href="/division/{{ $division->divalias }}" class="btn btn-outline-secondary btn-sm mr-2">Public Page →</a>
        <a href="/admin/divisions/{{ $division->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
    </div>
</div>

{{-- Details --}}
<div class="card mb-3">
    <div class="card-header">Details</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Alias</dt>
            <dd class="col-sm-9"><code>{{ $division->divalias }}</code></dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $division->divdescr ?? '—' }}</dd>

            <dt class="col-sm-3">Motto</dt>
            <dd class="col-sm-9">{{ $division->motto ?? '—' }}</dd>

            <dt class="col-sm-3">Color</dt>
            <dd class="col-sm-9">
                @if($division->color)
                    <span class="div-color-swatch" style="background-color: {{ $division->color }};"></span>
                    {{ $division->color }}
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-3">Division Leader</dt>
            <dd class="col-sm-9">
                @if($leaderName)
                    <a href="/profile/{{ $leaderName->rname }}">{{ $leaderName->rname }}</a>
                    @if($leaderName->dname) <small class="text-muted">({{ $leaderName->dname }})</small> @endif
                @else
                    <span class="text-muted">Vacant</span>
                @endif
            </dd>

            <dt class="col-sm-3">Division Second 1</dt>
            <dd class="col-sm-9">
                @if($sec1Name)
                    <a href="/profile/{{ $sec1Name->rname }}">{{ $sec1Name->rname }}</a>
                    @if($sec1Name->dname) <small class="text-muted">({{ $sec1Name->dname }})</small> @endif
                @else
                    <span class="text-muted">Vacant</span>
                @endif
            </dd>

            <dt class="col-sm-3">Division Second 2</dt>
            <dd class="col-sm-9">
                @if($sec2Name)
                    <a href="/profile/{{ $sec2Name->rname }}">{{ $sec2Name->rname }}</a>
                    @if($sec2Name->dname) <small class="text-muted">({{ $sec2Name->dname }})</small> @endif
                @else
                    <span class="text-muted">Vacant</span>
                @endif
            </dd>

            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">{{ $division->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
        </dl>
    </div>
</div>

{{-- Member management --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Members ({{ $members->count() }})</span>
    </div>
    <div class="card-body">

        {{-- Add member --}}
        <div class="mb-3">
            <label class="d-block mb-1" style="color:#c9a0a0; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em;">Add Member</label>
            <div class="d-flex">
                <div class="knight-search-wrapper" style="flex: 0 0 300px;">
                    <input type="text"
                           id="knightSearchInput"
                           class="form-control form-control-sm"
                           placeholder="Type 3+ characters to search…"
                           autocomplete="off">
                    <div id="knightSearchResults"></div>
                </div>
                <form method="POST" action="/admin/divisions/{{ $division->pkey }}/members" id="addMemberForm" class="ml-2">
                    @csrf
                    <input type="hidden" name="knight_pkey" id="selectedKnightPkey">
                    <button type="submit" id="addMemberBtn" class="btn btn-sm btn-primary" disabled>Add</button>
                </form>
            </div>
            <div id="selectedKnightLabel" class="mt-1 small text-muted"></div>
        </div>

        {{-- Member list --}}
        @if($members->count())
        <table class="member-table">
            <thead>
                <tr>
                    <th>Reddit Name</th>
                    <th>Discord Name</th>
                    <th>Rank</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                <tr>
                    <td><a href="/profile/{{ $member->rname }}" style="color:#efefef;">{{ $member->rname }}</a></td>
                    <td>{{ $member->dname ?? '—' }}</td>
                    <td>{{ $member->rankname ?? '—' }}</td>
                    <td>
                        <form method="POST" action="/admin/divisions/{{ $division->pkey }}/members/{{ $member->pivot_pkey }}/remove" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    data-toggle="confirmation"
                                    data-title="Remove {{ $member->rname }} from this division?"
                                    data-btn-ok-label="Remove"
                                    data-btn-ok-class="btn-danger"
                                    data-btn-cancel-label="Cancel">
                                Remove
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p class="text-muted small mb-0">No members yet.</p>
        @endif

    </div>
</div>

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">
            <form method="POST" action="/admin/divisions/{{ $division->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $division->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $division->activeflg ? 'Deactivate this division?' : 'Activate this division?' }}"
                        data-btn-ok-label="{{ $division->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $division->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $division->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            @if(!$division->delflg)
            <form method="POST" action="/admin/divisions/{{ $division->pkey }}/delete" class="d-inline ml-2">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        data-toggle="confirmation"
                        data-title="Delete this division?"
                        data-btn-ok-label="Delete"
                        data-btn-ok-class="btn-danger"
                        data-btn-cancel-label="Cancel">
                    Delete
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var $input    = document.getElementById('knightSearchInput');
    var $results  = document.getElementById('knightSearchResults');
    var $pkeyIn   = document.getElementById('selectedKnightPkey');
    var $addBtn   = document.getElementById('addMemberBtn');
    var $label    = document.getElementById('selectedKnightLabel');
    var divPkey   = {{ $division->pkey }};
    var timer     = null;

    $input.addEventListener('input', function () {
        var q = this.value.trim();
        clearTimeout(timer);

        // Clear selection if user types again
        $pkeyIn.value = '';
        $addBtn.disabled = true;
        $label.textContent = '';

        if (q.length < 3) {
            $results.style.display = 'none';
            $results.innerHTML = '';
            return;
        }

        timer = setTimeout(function () {
            fetch('/admin/knights/search?q=' + encodeURIComponent(q) + '&exclude_division=' + divPkey)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    $results.innerHTML = '';
                    if (!data.length) {
                        $results.innerHTML = '<div class="knight-result-item text-muted">No results</div>';
                    } else {
                        data.forEach(function (k) {
                            var item = document.createElement('div');
                            item.className = 'knight-result-item';
                            item.textContent = k.rname + (k.dname ? ' (' + k.dname + ')' : '');
                            item.addEventListener('click', function () {
                                $pkeyIn.value       = k.pkey;
                                $input.value        = k.rname;
                                $label.textContent  = 'Selected: ' + k.rname + (k.dname ? ' (' + k.dname + ')' : '');
                                $addBtn.disabled    = false;
                                $results.style.display = 'none';
                            });
                            $results.appendChild(item);
                        });
                    }
                    $results.style.display = 'block';
                });
        }, 300);
    });

    // Close results on outside click
    document.addEventListener('click', function (e) {
        if (!$input.contains(e.target) && !$results.contains(e.target)) {
            $results.style.display = 'none';
        }
    });
})();
</script>

@endsection