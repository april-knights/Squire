@extends('layouts.app')
@section('title', 'Admin — ' . $badge->bdg_title)
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
.badge-preview { max-width: 128px; max-height: 128px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.25rem; background: rgba(0,0,0,0.2); }
.typcd-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 3px; background-color: rgba(139,58,58,0.4); color: #c9a0a0; border: 1px solid #8b3a3a; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/badges">Badges</a></li>
        <li class="breadcrumb-item active">{{ $badge->bdg_title }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $badge->bdg_title }}
        <span class="typcd-badge ml-2">{{ $badge->typcd }}</span>
        @if($badge->delflg)
            <span class="badge badge-danger ml-1">Deleted</span>
        @elseif(!$badge->activeflg)
            <span class="badge badge-warning ml-1">Inactive</span>
        @else
            <span class="badge badge-success ml-1">Active</span>
        @endif
    </h2>
    <a href="/admin/badges/{{ $badge->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

<div class="row">
    <div class="col-md-2 text-center mb-3">
        <img src="{{ asset($badge->imgurl ?? 'static/img/badges/NoArtYet.jpg') }}"
             class="badge-preview" alt="{{ $badge->bdg_title }}">
    </div>
    <div class="col-md-10">
        <div class="card mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Title</dt>
                    <dd class="col-sm-9">{{ $badge->bdg_title }}</dd>

                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ $badge->typcd }}</dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $badge->bdgdesc ?? '—' }}</dd>

                    <dt class="col-sm-3">Order ID</dt>
                    <dd class="col-sm-9">{{ $badge->orderid }}</dd>

                    <dt class="col-sm-3">Discord Role ID</dt>
                    <dd class="col-sm-9">{{ $badge->roleid ?? '—' }}</dd>

                    <dt class="col-sm-3">Image URL</dt>
                    <dd class="col-sm-9"><code>{{ $badge->imgurl ?? '(none — using placeholder)' }}</code></dd>

                    <dt class="col-sm-3">Awarded To</dt>
                    <dd class="col-sm-9">
                        @if($knight_count > 0)
                            {{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3">Last Modified</dt>
                    <dd class="col-sm-9">{{ $badge->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">
            <form method="POST" action="/admin/badges/{{ $badge->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $badge->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $badge->activeflg ? 'Deactivate this badge?' : 'Activate this badge?' }}"
                        data-btn-ok-label="{{ $badge->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $badge->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $badge->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            @if(!$badge->delflg)
            <form method="POST" action="/admin/badges/{{ $badge->pkey }}/delete" class="d-inline ml-2">
                @csrf
                @if($knight_count > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                            title="Cannot delete — {{ $knight_count }} knight(s) have been awarded this badge">
                        Delete ({{ $knight_count }} awarded)
                    </button>
                @else
                    <button type="submit"
                            class="btn btn-sm btn-outline-danger"
                            data-toggle="confirmation"
                            data-title="Delete this badge?"
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