@extends('layouts.app')
@section('title', 'Admin — Security Profiles')
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
#secTable thead th {
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
        <li class="breadcrumb-item active">Security Profiles</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Security Profiles</h2>
    <a href="/admin/security/create" class="btn btn-primary btn-sm">+ New Profile</a>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="secTable">
    <thead>
        <tr>
            <th><a href="/admin/security?sort=pkey&direction={{ $sort === 'pkey' ? $nd : 'asc' }}">ID @if($sort==='pkey')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/security?sort=secname&direction={{ $sort === 'secname' ? $nd : 'asc' }}">Name @if($sort==='secname')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Description</th>
            <th>Knights</th>
            <th><a href="/admin/security?sort=activeflg&direction={{ $sort === 'activeflg' ? $nd : 'asc' }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($profiles as $profile)
        @php
            $isDeleted  = (bool)$profile->delflg;
            $isInactive = !(bool)$profile->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
            $count      = $counts[$profile->pkey] ?? 0;
        @endphp
        <tr class="{{ $rowClass }}">
            <td>{{ $profile->pkey }}</td>
            <td><a href="/admin/security/{{ $profile->pkey }}">{{ $profile->secname }}</a></td>
            <td>{{ $profile->secdescr }}</td>
            <td>
                @if($count > 0)
                    <a href="/admin/knights?security={{ $profile->pkey }}">{{ $count }}</a>
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
                <a href="/admin/security/{{ $profile->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-muted">No profiles found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection