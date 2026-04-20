@extends('layouts.app')
@section('title', 'Admin — Delete Image')
@section('full_width', true)

@push('styles')
<style>
.card { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.card-header { background-color: rgba(0,0,0,0.3); border-bottom: 1px solid #8b3a3a; color: #efefef; font-weight: 600; }
.card-body { color: #efefef; }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.img-preview { max-width: 150px; max-height: 150px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.4rem; background: rgba(0,0,0,0.2); }
.ref-item { padding: 0.3rem 0; border-bottom: 1px solid rgba(139,58,58,0.2); font-size: 0.875rem; color: #efefef; }
.ref-item:last-child { border-bottom: none; }
.warning-card { border-color: #c8a000; }
.warning-card .card-header { color: #c8a000; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/images">Images</a></li>
        <li class="breadcrumb-item active">Delete</li>
    </ol>
</nav>

<h2 class="mb-3">Delete Image</h2>

<div class="row">
    <div class="col-md-2 mb-3">
        <img src="{{ $urlPath }}" class="img-preview" alt="{{ $filename }}"
             onerror="this.src='/static/img/badges/NoArtYet.jpg'">
        <div class="small text-muted mt-2">{{ $filename }}</div>
        <div class="small text-muted"><code>{{ $path }}</code></div>
    </div>

    <div class="col-md-10">

        {{-- DB reference warnings --}}
        @if($badgeRefs->count() || $linkRefs->count())
        <div class="card warning-card mb-3">
            <div class="card-header">⚠ This image is referenced in the database</div>
            <div class="card-body">
                <p class="text-muted small">Deleting this file will break the following references. Consider updating them before deleting.</p>

                @if($badgeRefs->count())
                <strong style="color:#c9a0a0;">Badges ({{ $badgeRefs->count() }})</strong>
                @foreach($badgeRefs as $ref)
                <div class="ref-item">
                    <a href="/admin/badges/{{ $ref->pkey }}" style="color:#efefef;">{{ $ref->bdg_title }}</a>
                    <span class="text-muted small ml-2"><code>{{ $ref->imgurl }}</code></span>
                </div>
                @endforeach
                @endif

                @if($linkRefs->count())
                <strong style="color:#c9a0a0;" class="mt-2 d-block">Links ({{ $linkRefs->count() }})</strong>
                @foreach($linkRefs as $ref)
                <div class="ref-item">
                    <a href="/admin/links/{{ $ref->pkey }}" style="color:#efefef;">{{ $ref->linknm }}</a>
                    <span class="text-muted small ml-2"><code>{{ $ref->imgurl }}</code></span>
                </div>
                @endforeach
                @endif
            </div>
        </div>
        @else
        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-0"><i class="fas fa-check text-success mr-2"></i>No database references found for this image — safe to delete.</p>
            </div>
        </div>
        @endif

        {{-- Confirm delete form --}}
        <div class="card mb-3" style="border-color: #6a1a1a;">
            <div class="card-header" style="color: #e57373;">Confirm Deletion</div>
            <div class="card-body">
                <p class="text-muted small">This action is permanent and cannot be undone. The file will be removed from the server.</p>
                <form method="POST" action="/admin/images/delete">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}">
                    <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                    <a href="/admin/images" class="btn btn-outline-secondary btn-sm ml-2">Cancel</a>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection