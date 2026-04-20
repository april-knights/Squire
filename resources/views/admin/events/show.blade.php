@extends('layouts.app')
@section('title', 'Admin — ' . $event->title)
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
.type-badge { font-size: 0.8rem; padding: 0.25rem 0.5rem; border-radius: 3px; border: 1px solid #8b3a3a; }
.type-reddit    { background-color: rgba(255,69,0,0.2); color: #ff8c69; border-color: #ff4500; }
.type-internal  { background-color: rgba(45,106,45,0.2); color: #4caf50; border-color: #2d6a2d; }
.type-scheduled { background-color: rgba(33,150,243,0.2); color: #64b5f6; border-color: #2196f3; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/events">Events</a></li>
        <li class="breadcrumb-item active">{{ $event->title }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $event->title }}
        <span class="type-badge type-{{ $event->eventtype }} ml-2">{{ $event->eventtype }}</span>
        @if($event->delflg)
            <span class="badge badge-danger ml-1">Deleted</span>
        @elseif(!$event->activeflg)
            <span class="badge badge-warning ml-1">Inactive</span>
        @else
            <span class="badge badge-success ml-1">Active</span>
        @endif
    </h2>
    <a href="/admin/events/{{ $event->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

@php
    $livedate = $event->livedate && $event->livedate !== '0000-00-00' ? $event->livedate : null;
    $enddate  = $event->enddate  && $event->enddate  !== '0000-00-00' ? $event->enddate  : null;
@endphp

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Title</dt>
            <dd class="col-sm-9">{{ $event->title }}</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $event->eventdescr ?? '—' }}</dd>

            <dt class="col-sm-3">Live Date</dt>
            <dd class="col-sm-9">{{ $livedate ?? '—' }}</dd>

            <dt class="col-sm-3">End Date</dt>
            <dd class="col-sm-9">{{ $enddate ?? '—' }}</dd>

            <dt class="col-sm-3">Organizer</dt>
            <dd class="col-sm-9">{{ $event->organizer ?? '—' }}</dd>

            <dt class="col-sm-3">Type</dt>
            <dd class="col-sm-9"><span class="type-badge type-{{ $event->eventtype }}">{{ $event->eventtype }}</span></dd>

            <dt class="col-sm-3">Show on Profiles</dt>
            <dd class="col-sm-9">
                @if($event->profileflg)
                    <i class="fas fa-check text-success"></i> Yes — selectable as first event on knight profiles
                @else
                    <i class="fas fa-times text-muted"></i> No — hidden from profile cohort selector
                @endif
            </dd>

            <dt class="col-sm-3">Cohort Size</dt>
            <dd class="col-sm-9">
                @if($knight_count > 0)
                    {{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }} joined during this event
                @else
                    <span class="text-muted">No knights have this as their first event</span>
                @endif
            </dd>

            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">{{ $event->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
        </dl>
    </div>
</div>

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">
            <form method="POST" action="/admin/events/{{ $event->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $event->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $event->activeflg ? 'Deactivate this event?' : 'Activate this event?' }}"
                        data-btn-ok-label="{{ $event->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $event->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $event->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            @if(!$event->delflg)
            <form method="POST" action="/admin/events/{{ $event->pkey }}/delete" class="d-inline ml-2">
                @csrf
                @if($knight_count > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                            title="Cannot delete — {{ $knight_count }} knight(s) have this as their first event">
                        Delete ({{ $knight_count }} in cohort)
                    </button>
                @else
                    <button type="submit"
                            class="btn btn-sm btn-outline-danger"
                            data-toggle="confirmation"
                            data-title="Delete this event?"
                            data-btn-ok-label="Delete"
                            data-btn-ok-class="btn-danger"
                            data-btn-cancel-label="Cancel">
                        Delete
                    </button>
                @endif
            </form>
            @endif
        </div>
    </div>
</div>

@endsection