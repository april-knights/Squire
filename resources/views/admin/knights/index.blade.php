@extends('layouts.app')
@section('title', 'Admin — Knights')
@section('full_width', true)
@section('content')

@push('styles')
<style>
/* Scrollable table container — horizontal bar stays docked at viewport bottom */
.admin-table-container {
    max-height: 72vh;
    overflow-x: auto;
    overflow-y: auto;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
}

/* Sticky header */
#knightTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #5a2424;
    border-bottom: 2px solid #8b3a3a;
    white-space: nowrap;
}

/* Row states */
#knightTable tr.row-inactive {
    opacity: 0.6;
    border-left: 3px solid #c8a000;
}
#knightTable tr.row-deleted {
    opacity: 0.45;
    border-left: 3px solid #8b2020;
    text-decoration: line-through;
}

/* Status badges */
.badge-active   { background-color: #2d6a2d; color: #fff; }
.badge-inactive { background-color: #7a6a00; color: #fff; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; }

/* Breadcrumb dark theme */
.breadcrumb {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
}
.breadcrumb-item a        { color: #efefef; }
.breadcrumb-item.active   { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
</style>
@endpush

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Knights</li>
    </ol>
</nav>

<h2>Knight Management</h2>
<p class="text-muted">All knights including inactive and deleted records.</p>

{{-- Search --}}
<div class="row mb-3">
    <div class="col-md-5">
        <input type="text"
               id="knightSearch"
               class="form-control"
               placeholder="Search by Reddit name, Discord name, Discord ID, email, battalion, or skill…">
    </div>
    <div class="col-md-7 d-flex align-items-center">
        <div class="form-check form-check-inline ml-2">
            <input class="form-check-input" type="checkbox" id="filterInactive">
            <label class="form-check-label" for="filterInactive">Hide inactive</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="filterDeleted">
            <label class="form-check-label" for="filterDeleted">Hide deleted</label>
        </div>
    </div>
</div>

{{-- Sort helpers --}}
@php
    $nd = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    $sh = function($col) use ($sort, $nd, $icon) {
        $dir = $sort === $col ? $nd : 'asc';
        $arrow = $sort === $col ? " <i class='fas {$icon}'></i>" : '';
        return "/admin/knights?sort={$col}&direction={$dir}";
    };
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="knightTable">
    <thead>
        <tr>
            <th><a href="{{ $sh('rname') }}">Reddit Name @if($sort==='rname')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('dname') }}">Discord Name @if($sort==='dname')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('batt') }}">Battalion @if($sort==='batt')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('rnk') }}">Rank @if($sort==='rnk')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('security') }}">Security @if($sort==='security')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('last_login') }}">Last Login @if($sort==='last_login')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="{{ $sh('activeflg') }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($knights as $knight)
        @php
            $isDeleted  = (bool)$knight->delflg;
            $isInactive = !$knight->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');

            if ($isDeleted) {
                $statusBadge = '<span class="badge badge-deleted">Deleted</span>';
            } elseif ($isInactive) {
                $statusBadge = '<span class="badge badge-inactive">Inactive</span>';
            } else {
                $statusBadge = '<span class="badge badge-active">Active</span>';
            }

            $skillList = $knight->skills->pluck('skillname')->implode(' ');
        @endphp
        <tr class="{{ $rowClass }}"
            data-rname="{{ strtolower($knight->rname) }}"
            data-dname="{{ strtolower($knight->dname ?? '') }}"
            data-discordid="{{ strtolower($knight->discordid ?? '') }}"
            data-email="{{ strtolower($knight->email ?? '') }}"
            data-battalion="{{ strtolower($knight->battalion?->name ?? '') }}"
            data-skills="{{ strtolower($skillList) }}"
            data-active="{{ $knight->activeflg }}"
            data-deleted="{{ $knight->delflg }}">
            <td><a href="/admin/knights/{{ $knight->pkey }}">{{ $knight->rname }}</a></td>
            <td>{{ $knight->dname }}</td>
            <td>{{ $knight->battalion?->name ?? '—' }}</td>
            <td>{{ $knight->rank?->name ?? '—' }}</td>
            <td>{{ $knight->secname ?? '—' }}</td>
            <td>{{ $knight->last_login ? \Carbon\Carbon::parse($knight->last_login)->diffForHumans() : '—' }}</td>
            <td>{!! $statusBadge !!}</td>
            <td><a href="/admin/knights/{{ $knight->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-muted">No knights found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

<p class="text-muted small" id="knightCount"></p>

<script>
(function () {
    var $search    = document.getElementById('knightSearch');
    var $hideInact = document.getElementById('filterInactive');
    var $hideDel   = document.getElementById('filterDeleted');
    var $count     = document.getElementById('knightCount');
    var rows       = Array.from(document.querySelectorAll('#knightTable tbody tr'));

    function applyFilters() {
        var search  = $search.value.toLowerCase().trim();
        var hideIn  = $hideInact.checked;
        var hideDel = $hideDel.checked;
        var visible = 0;

        rows.forEach(function (row) {
            var d = row.dataset;
            // Text search across all indexed fields
            var matchText = !search || [
                d.rname, d.dname, d.discordid, d.email, d.battalion, d.skills
            ].some(function (val) { return val && val.includes(search); });

            // Flag filters
            var matchActive  = !hideIn  || d.active  === '1';
            var matchDeleted = !hideDel || d.deleted  === '0';

            var show = matchText && matchActive && matchDeleted;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        $count.textContent = 'Showing ' + visible + ' of ' + rows.length + ' knights.';
    }

    $search.addEventListener('input', applyFilters);
    $hideInact.addEventListener('change', applyFilters);
    $hideDel.addEventListener('change', applyFilters);

    // Initial count
    applyFilters();
})();
</script>

@endsection