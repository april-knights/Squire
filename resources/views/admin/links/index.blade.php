@extends('layouts.app')
@section('title', 'Admin — Links')
@section('full_width', true)

@push('styles')
<style>
.admin-table-container { max-height: 72vh; overflow-x: auto; overflow-y: auto; border: 1px solid #8b3a3a; border-radius: 4px; }
#linkTable thead th { position: sticky; top: 0; z-index: 10; background-color: #5a2424; border-bottom: 2px solid #8b3a3a; white-space: nowrap; }
.badge-active   { background-color: #2d6a2d; color: #fff; }
.badge-inactive { background-color: #7a6a00; color: #fff; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; }
tr.row-inactive { opacity: 0.6; border-left: 3px solid #c8a000; }
tr.row-deleted  { opacity: 0.45; border-left: 3px solid #8b2020; text-decoration: line-through; }
.link-thumb { width: 36px; height: 36px; object-fit: contain; }
.type-badge { font-size: 0.7rem; padding: 0.2rem 0.45rem; border-radius: 3px; background-color: rgba(139,58,58,0.3); color: #c9a0a0; border: 1px solid #8b3a3a; }
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
        <li class="breadcrumb-item active">Links</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Link Management</h2>
    <a href="/admin/links/create" class="btn btn-primary btn-sm">+ New Link</a>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="linkTable">
    <thead>
        <tr>
            <th>Image</th>
            <th><a href="/admin/links?sort=typcd&direction={{ $sort==='typcd' ? $nd : 'asc' }}">Type @if($sort==='typcd')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/links?sort=orderid&direction={{ $sort==='orderid' ? $nd : 'asc' }}">Order @if($sort==='orderid')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/links?sort=linknm&direction={{ $sort==='linknm' ? $nd : 'asc' }}">Name @if($sort==='linknm')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Description</th>
            <th>URL</th>
            <th><a href="/admin/links?sort=activeflg&direction={{ $sort==='activeflg' ? $nd : 'asc' }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($links as $link)
        @php
            $isDeleted  = (bool)$link->delflg;
            $isInactive = !(bool)$link->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
        @endphp
        <tr class="{{ $rowClass }}">
            <td>
                @if($link->imgurl)
                    <img src="{{ $link->imgurl }}" class="link-thumb" alt="{{ $link->linknm }}">
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td><span class="type-badge">{{ $link->typcd }}</span></td>
            <td>{{ $link->orderid }}</td>
            <td><a href="/admin/links/{{ $link->pkey }}">{{ $link->linknm }}</a></td>
            <td>{{ Str::limit($link->linkdesc, 60) }}</td>
            <td>
                @if($link->linkurl)
                    <a href="{{ trim($link->linkurl) }}" target="_blank" rel="noopener"
                       class="text-muted small">{{ Str::limit(trim($link->linkurl), 40) }}</a>
                @else
                    <span class="text-muted">—</span>
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
                <a href="/admin/links/{{ $link->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-muted">No links found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection