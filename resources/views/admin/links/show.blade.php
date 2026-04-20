@extends('layouts.app')
@section('title', 'Admin — ' . $link->linknm)
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
.link-preview-img { max-width: 80px; max-height: 80px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.25rem; background: rgba(0,0,0,0.2); }
.type-badge { font-size: 0.8rem; padding: 0.25rem 0.5rem; border-radius: 3px; background-color: rgba(139,58,58,0.3); color: #c9a0a0; border: 1px solid #8b3a3a; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/links">Links</a></li>
        <li class="breadcrumb-item active">{{ $link->linknm }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $link->linknm }}
        <span class="type-badge ml-2">{{ $link->typcd }}</span>
        @if($link->delflg)
            <span class="badge badge-danger ml-1">Deleted</span>
        @elseif(!$link->activeflg)
            <span class="badge badge-warning ml-1">Inactive</span>
        @else
            <span class="badge badge-success ml-1">Active</span>
        @endif
    </h2>
    <a href="/admin/links/{{ $link->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

<div class="row">
    @if($link->imgurl)
    <div class="col-md-1 mb-3">
        <img src="{{ $link->imgurl }}" class="link-preview-img" alt="{{ $link->linknm }}">
    </div>
    @endif
    <div class="{{ $link->imgurl ? 'col-md-11' : 'col-12' }}">
        <div class="card mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $link->linknm }}</dd>

                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ $link->typcd }}</dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $link->linkdesc }}</dd>

                    <dt class="col-sm-3">URL</dt>
                    <dd class="col-sm-9">
                        @if($link->linkurl)
                            <a href="{{ trim($link->linkurl) }}" target="_blank" rel="noopener">{{ trim($link->linkurl) }}</a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-3">Image URL</dt>
                    <dd class="col-sm-9"><code>{{ $link->imgurl ?? '—' }}</code></dd>

                    <dt class="col-sm-3">Last Modified</dt>
                    <dd class="col-sm-9">{{ $link->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
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
            <form method="POST" action="/admin/links/{{ $link->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $link->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $link->activeflg ? 'Deactivate this link?' : 'Activate this link?' }}"
                        data-btn-ok-label="{{ $link->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $link->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $link->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            @if(!$link->delflg)
            <form method="POST" action="/admin/links/{{ $link->pkey }}/delete" class="d-inline ml-2">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        data-toggle="confirmation"
                        data-title="Delete this link?"
                        data-btn-ok-label="Delete"
                        data-btn-ok-class="btn-danger"
                        data-btn-cancel-label="Cancel">
                    Delete
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<a href="/links" class="btn btn-outline-secondary btn-sm">View Public Links Page →</a>

@endsection