@extends('layouts.app')
@section('title', 'Admin — Divisions')
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
#divTable thead th {
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
.div-color-swatch { display: inline-block; width: 1rem; height: 1rem; border-radius: 2px; border: 1px solid rgba(255,255,255,0.2); vertical-align: middle; margin-right: 0.25rem; }
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
        <li class="breadcrumb-item active">Divisions</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Division Management</h2>
    <a href="/admin/divisions/create" class="btn btn-primary btn-sm">+ New Division</a>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="divTable">
    <thead>
        <tr>
            <th><a href="/admin/divisions?sort=name&direction={{ $sort === 'name' ? $nd : 'asc' }}">Name @if($sort==='name')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/divisions?sort=divalias&direction={{ $sort === 'divalias' ? $nd : 'asc' }}">Alias @if($sort==='divalias')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Color</th>
            <th>Leader</th>
            <th>Members</th>
            <th><a href="/admin/divisions?sort=activeflg&direction={{ $sort === 'activeflg' ? $nd : 'asc' }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($divisions as $div)
        @php
            $isDeleted  = (bool)$div->delflg;
            $isInactive = !(bool)$div->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
            $count      = $counts[$div->pkey] ?? 0;
        @endphp
        <tr class="{{ $rowClass }}">
            <td><a href="/admin/divisions/{{ $div->pkey }}">{{ $div->name }}</a></td>
            <td><code>{{ $div->divalias }}</code></td>
            <td>
                @if($div->color)
                    <span class="div-color-swatch" style="background-color: {{ $div->color }};"></span>
                    {{ $div->color }}
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>{{ $leaderNames[$div->pkey] ?? '—' }}</td>
            <td>{{ $count }}</td>
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
                <a href="/admin/divisions/{{ $div->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted">No divisions found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection