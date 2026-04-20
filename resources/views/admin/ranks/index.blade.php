@extends('layouts.app')
@section('title', 'Admin — Ranks')
@section('full_width', true)

@push('styles')
<style>
.admin-table-container {
    max-height: 72vh;
    overflow-x: auto;
    overflow-y: auto;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
}
#rankTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #5a2424;
    border-bottom: 2px solid #8b3a3a;
    white-space: nowrap;
}
.badge-active   { background-color: #2d6a2d; color: #fff; }
.badge-inactive { background-color: #7a6a00; color: #fff; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; }
tr.row-inactive { opacity: 0.6; border-left: 3px solid #c8a000; }
tr.row-deleted  { opacity: 0.45; border-left: 3px solid #8b2020; text-decoration: line-through; }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Ranks</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Rank Management</h2>
    <a href="/admin/ranks/create" class="btn btn-primary btn-sm">+ New Rank</a>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="rankTable">
    <thead>
        <tr>
            <th><a href="/admin/ranks?sort=rval&direction={{ $sort === 'rval' ? $nd : 'asc' }}">rval @if($sort==='rval')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/ranks?sort=name&direction={{ $sort === 'name' ? $nd : 'asc' }}">Name @if($sort==='name')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Description</th>
            <th>Unique</th>
            <th>Knights</th>
            <th><a href="/admin/ranks?sort=activeflg&direction={{ $sort === 'activeflg' ? $nd : 'asc' }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($ranks as $rank)
        @php
            $isDeleted  = (bool)$rank->delflg;
            $isInactive = !(bool)$rank->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
            $count      = $counts[$rank->pkey] ?? 0;
        @endphp
        <tr class="{{ $rowClass }}">
            <td>{{ $rank->rval }}</td>
            <td><a href="/admin/ranks/{{ $rank->pkey }}">{{ $rank->name }}</a></td>
            <td>{{ $rank->rankdescr ?? '—' }}</td>
            <td>
                @if($rank->uniqe)
                    <i class="fas fa-check text-success" title="Unique rank"></i>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                @if($count > 0)
                    <a href="/admin/knights?rnk={{ $rank->pkey }}">{{ $count }}</a>
                @else
                    <span class="text-muted">0</span>
                @endif
            </td>
            <td>
                @if($isDeleted)
                    <span class="badge badge-deleted">Deleted</span>
                @elseif($isInactive)
                    <span class="badge badge-inactive">Inactive</span>
                @else
                    <span class="badge badge-active">Active</span>
                @endif
            </td>
            <td>
                <a href="/admin/ranks/{{ $rank->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted">No ranks found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection