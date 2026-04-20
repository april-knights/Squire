@extends('layouts.app')
@section('title', 'Admin — ' . $knight->rname)
@section('full_width', true)

@push('styles')
<style>
.card {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
}
.card-header {
    background-color: rgba(0,0,0,0.3);
    border-bottom: 1px solid #8b3a3a;
    color: #efefef;
    font-weight: 600;
}
.card-body {
    color: #efefef;
}
dt { color: #c9a0a0; }
dd { color: #efefef; }
.breadcrumb {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
}
.breadcrumb-item a        { color: #efefef; }
.breadcrumb-item.active   { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/knights">Knights</a></li>
        <li class="breadcrumb-item active">{{ $knight->rname }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $knight->rname }}
        @if($knight->delflg)
            <span class="badge badge-danger">Deleted</span>
        @elseif(!$knight->activeflg)
            <span class="badge badge-warning">Inactive</span>
        @else
            <span class="badge badge-success">Active</span>
        @endif
    </h2>
    <a href="/admin/knights/{{ $knight->pkey }}/edit" class="btn btn-secondary">Edit</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Reddit Name</dt>
            <dd class="col-sm-9">{{ $knight->rname }}</dd>

            <dt class="col-sm-3">Discord Name</dt>
            <dd class="col-sm-9">{{ $knight->dname ?? '—' }}</dd>

            <dt class="col-sm-3">Discord ID</dt>
            <dd class="col-sm-9">{{ $knight->discordid ?? '—' }}</dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $knight->email ?? '—' }}</dd>

            <dt class="col-sm-3">Battalion</dt>
            <dd class="col-sm-9">{{ $knight->battalion?->name ?? '—' }}</dd>

            <dt class="col-sm-3">Rank</dt>
            <dd class="col-sm-9">{{ $knight->rank?->name ?? '—' }}</dd>

            <dt class="col-sm-3">Security Profile</dt>
            <dd class="col-sm-9">{{ $knight->secname ?? '—' }}</dd>

            <dt class="col-sm-3">Internal Transfer</dt>
            <dd class="col-sm-9">{{ $knight->inttrans ?? '—' }}</dd>

            <dt class="col-sm-3">Officer Note</dt>
            <dd class="col-sm-9">{{ $knight->onote ?? '—' }}</dd>

            <dt class="col-sm-3">Last Login</dt>
            <dd class="col-sm-9">
                {{ $knight->last_login ? \Carbon\Carbon::parse($knight->last_login)->format('Y-m-d H:i') . ' (' . \Carbon\Carbon::parse($knight->last_login)->diffForHumans() . ')' : '—' }}
            </dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $knight->crtsetdt?->format('Y-m-d') ?? '—' }}</dd>

            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">
                {{ $knight->lstmdts?->format('Y-m-d H:i') ?? '—' }}
                {{ $lstmdby_name ? 'by ' . $lstmdby_name : '' }}
            </dd>
        </dl>
    </div>
</div>

{{-- Skills --}}
@if($knight->skills->count())
<div class="card mb-3">
    <div class="card-header">Skills</div>
    <div class="card-body">
        {{ $knight->skills->pluck('skillname')->implode(', ') }}
    </div>
</div>
@endif

{{-- Divisions --}}
@if($knight->divisions->count())
<div class="card mb-3">
    <div class="card-header">Divisions</div>
    <div class="card-body">
        {{ $knight->divisions->pluck('name')->implode(', ') }}
    </div>
</div>
@endif

{{-- Status toggles --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">

            {{-- Toggle activeflg --}}
            <form method="POST" action="/admin/knights/{{ $knight->pkey }}/toggle" class="d-inline">
                @csrf
                <input type="hidden" name="field" value="activeflg">
                <button type="submit"
                        class="btn {{ $knight->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-btn-ok-label="{{ $knight->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $knight->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel"
                        data-title="{{ $knight->activeflg ? 'Deactivate this knight?' : 'Reactivate this knight?' }}">
                    {{ $knight->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            {{-- Toggle delflg --}}
            <form method="POST" action="/admin/knights/{{ $knight->pkey }}/toggle" class="d-inline ml-2">
                @csrf
                <input type="hidden" name="field" value="delflg">
                <button type="submit"
                        class="btn {{ $knight->delflg ? 'btn-outline-success' : 'btn-outline-danger' }}"
                        data-toggle="confirmation"
                        data-btn-ok-label="{{ $knight->delflg ? 'Undelete' : 'Delete' }}"
                        data-btn-ok-class="btn-{{ $knight->delflg ? 'success' : 'danger' }}"
                        data-btn-cancel-label="Cancel"
                        data-title="{{ $knight->delflg ? 'Restore this knight?' : 'Mark this knight as deleted?' }}">
                    {{ $knight->delflg ? 'Undelete' : 'Delete' }}
                </button>
            </form>

        </div>
    </div>
</div>

<a href="/profile/{{ $knight->rname }}" class="btn btn-outline-secondary btn-sm">View Public Profile →</a>

@endsection