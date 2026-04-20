@extends('layouts.app')
@section('title', 'Admin — Badges')
@section('full_width', true)

@push('styles')
<style>
.admin-table-container { max-height: 72vh; overflow-x: auto; overflow-y: auto; border: 1px solid #8b3a3a; border-radius: 4px; }
#badgeTable thead th { position: sticky; top: 0; z-index: 10; background-color: #5a2424; border-bottom: 2px solid #8b3a3a; white-space: nowrap; }
.badge-active   { background-color: #2d6a2d; color: #fff; }
.badge-inactive { background-color: #7a6a00; color: #fff; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; }
tr.row-inactive { opacity: 0.6; border-left: 3px solid #c8a000; }
tr.row-deleted  { opacity: 0.45; border-left: 3px solid #8b2020; text-decoration: line-through; }
.badge-thumb { width: 40px; height: 40px; object-fit: contain; }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.form-control { background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef; }
.typcd-badge { font-size: 0.7rem; padding: 0.2rem 0.45rem; border-radius: 3px; background-color: rgba(139,58,58,0.4); color: #c9a0a0; border: 1px solid #8b3a3a; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Badges</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Badge Management</h2>
    <a href="/admin/badges/create" class="btn btn-primary btn-sm">+ New Badge</a>
</div>

{{-- Type filter --}}
<div class="mb-3 d-flex align-items-center">
    <label class="mr-2 text-muted small">Filter by type:</label>
    <div>
        <a href="/admin/badges" class="btn btn-sm {{ !$typeFilter ? 'btn-secondary' : 'btn-outline-secondary' }} mr-1">All</a>
        @foreach($typecds as $t)
        <a href="/admin/badges?typcd={{ $t }}"
           class="btn btn-sm {{ $typeFilter === $t ? 'btn-secondary' : 'btn-outline-secondary' }} mr-1">{{ $t }}</a>
        @endforeach
    </div>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    $qs   = $typeFilter ? '&typcd=' . $typeFilter : '';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="badgeTable">
    <thead>
        <tr>
            <th>Image</th>
            <th><a href="/admin/badges?sort=orderid&direction={{ $sort==='orderid' ? $nd : 'asc' }}{{ $qs }}">Order @if($sort==='orderid')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/badges?sort=bdg_title&direction={{ $sort==='bdg_title' ? $nd : 'asc' }}{{ $qs }}">Title @if($sort==='bdg_title')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/badges?sort=typcd&direction={{ $sort==='typcd' ? $nd : 'asc' }}{{ $qs }}">Type @if($sort==='typcd')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Awarded</th>
            <th><a href="/admin/badges?sort=activeflg&direction={{ $sort==='activeflg' ? $nd : 'asc' }}{{ $qs }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($badges as $badge)
        @php
            $isDeleted  = (bool)$badge->delflg;
            $isInactive = !(bool)$badge->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
            $count      = $counts[$badge->pkey] ?? 0;
        @endphp
        <tr class="{{ $rowClass }}">
            <td>
                <img src="{{ asset($badge->imgurl ?? 'static/img/badges/NoArtYet.jpg') }}"
                     class="badge-thumb" alt="{{ $badge->bdg_title }}">
            </td>
            <td>{{ $badge->orderid }}</td>
            <td><a href="/admin/badges/{{ $badge->pkey }}">{{ $badge->bdg_title }}</a></td>
            <td><span class="typcd-badge">{{ $badge->typcd }}</span></td>
            <td>
                @if($count > 0)
                    <span title="{{ $count }} knight(s)">{{ $count }}</span>
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
                <a href="/admin/badges/{{ $badge->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted">No badges found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection