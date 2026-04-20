@extends('layouts.app')
@section('title', 'Admin — Events')
@section('full_width', true)

@push('styles')
<style>
.admin-table-container { max-height: 72vh; overflow-x: auto; overflow-y: auto; border: 1px solid #8b3a3a; border-radius: 4px; }
#eventTable thead th { position: sticky; top: 0; z-index: 10; background-color: #5a2424; border-bottom: 2px solid #8b3a3a; white-space: nowrap; }
.badge-active   { background-color: #2d6a2d; color: #fff; }
.badge-inactive { background-color: #7a6a00; color: #fff; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; }
tr.row-inactive { opacity: 0.6; border-left: 3px solid #c8a000; }
tr.row-deleted  { opacity: 0.45; border-left: 3px solid #8b2020; text-decoration: line-through; }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.type-badge { font-size: 0.7rem; padding: 0.2rem 0.45rem; border-radius: 3px; border: 1px solid #8b3a3a; }
.type-reddit   { background-color: rgba(255,69,0,0.2); color: #ff8c69; border-color: #ff4500; }
.type-internal { background-color: rgba(45,106,45,0.2); color: #4caf50; border-color: #2d6a2d; }
.type-scheduled { background-color: rgba(33,150,243,0.2); color: #64b5f6; border-color: #2196f3; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Events</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Event Management</h2>
    <a href="/admin/events/create" class="btn btn-primary btn-sm">+ New Event</a>
</div>

@php
    $nd   = $direction === 'asc' ? 'desc' : 'asc';
    $icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp

<div class="admin-table-container">
<table class="table table-sm table-hover table-borderless" id="eventTable">
    <thead>
        <tr>
            <th><a href="/admin/events?sort=livedate&direction={{ $sort==='livedate' ? $nd : 'desc' }}">Live Date @if($sort==='livedate')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/events?sort=title&direction={{ $sort==='title' ? $nd : 'asc' }}">Title @if($sort==='title')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/events?sort=organizer&direction={{ $sort==='organizer' ? $nd : 'asc' }}">Organizer @if($sort==='organizer')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th><a href="/admin/events?sort=eventtype&direction={{ $sort==='eventtype' ? $nd : 'asc' }}">Type @if($sort==='eventtype')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Profile</th>
            <th>Cohort</th>
            <th><a href="/admin/events?sort=activeflg&direction={{ $sort==='activeflg' ? $nd : 'asc' }}">Status @if($sort==='activeflg')<i class="fas {{ $icon }}"></i>@endif</a></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($events as $event)
        @php
            $isDeleted  = (bool)$event->delflg;
            $isInactive = !(bool)$event->activeflg && !$isDeleted;
            $rowClass   = $isDeleted ? 'row-deleted' : ($isInactive ? 'row-inactive' : '');
            $count      = $counts[$event->pkey] ?? 0;
            $livedate   = $event->livedate && $event->livedate !== '0000-00-00' ? $event->livedate : null;
        @endphp
        <tr class="{{ $rowClass }}">
            <td>{{ $livedate ?? '—' }}</td>
            <td><a href="/admin/events/{{ $event->pkey }}">{{ $event->title }}</a></td>
            <td>{{ $event->organizer ?? '—' }}</td>
            <td><span class="type-badge type-{{ $event->eventtype }}">{{ $event->eventtype }}</span></td>
            <td>
                @if($event->profileflg)
                    <i class="fas fa-check text-success" title="Shows on profiles"></i>
                @else
                    <i class="fas fa-times text-muted" title="Hidden from profiles"></i>
                @endif
            </td>
            <td>
                @if($count > 0)
                    <span title="{{ $count }} knight(s) first joined during this event">{{ $count }}</span>
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
                <a href="/admin/events/{{ $event->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-muted">No events found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection