@extends('layouts.app')
@section('title', 'Admin — ' . $skill->skillname)
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
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/skills">Skills</a></li>
        @if($parent)
        <li class="breadcrumb-item"><a href="/admin/skills/{{ $parent->pkey }}">{{ $parent->skillname }}</a></li>
        @endif
        <li class="breadcrumb-item active">{{ $skill->skillname }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $skill->skillname }}
        @if(!$skill->parentid) <span class="badge badge-secondary ml-1">Group</span> @endif
        @if($skill->delflg)
            <span class="badge badge-danger ml-1">Deleted</span>
        @elseif(!$skill->activeflg)
            <span class="badge badge-warning ml-1">Inactive</span>
        @else
            <span class="badge badge-success ml-1">Active</span>
        @endif
    </h2>
    <a href="/admin/skills/{{ $skill->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $skill->skillname }}</dd>

            <dt class="col-sm-3">Type</dt>
            <dd class="col-sm-9">
                @if($parent)
                    Skill — in group <a href="/admin/skills/{{ $parent->pkey }}">{{ $parent->skillname }}</a>
                @else
                    Group (top-level)
                @endif
            </dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $skill->skilldescr ?? '—' }}</dd>

            <dt class="col-sm-3">Public</dt>
            <dd class="col-sm-9">
                @if($skill->public)
                    <i class="fas fa-check text-success"></i> Visible on public profiles
                @else
                    <i class="fas fa-times text-muted"></i> Hidden from public profiles
                @endif
            </dd>

            <dt class="col-sm-3">Knights Assigned</dt>
            <dd class="col-sm-9">
                @if($knight_count > 0)
                    {{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}
                @else
                    <span class="text-muted">None</span>
                @endif
            </dd>

            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">{{ $skill->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
        </dl>
    </div>
</div>

{{-- Children (if group) --}}
@if(!$skill->parentid && $children->count())
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Skills in this group ({{ $children->count() }})</span>
        <a href="/admin/skills/create?parent={{ $skill->pkey }}" class="btn btn-sm btn-outline-secondary">+ Add Skill</a>
    </div>
    <div class="card-body p-0">
        @foreach($children as $child)
        <div class="d-flex justify-content-between align-items-center px-3 py-2"
             style="border-bottom: 1px solid rgba(139,58,58,0.2);">
            <a href="/admin/skills/{{ $child->pkey }}" style="color:#efefef;">{{ $child->skillname }}</a>
            <a href="/admin/skills/{{ $child->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">
            <form method="POST" action="/admin/skills/{{ $skill->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $skill->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $skill->activeflg ? 'Deactivate this skill?' : 'Activate this skill?' }}"
                        data-btn-ok-label="{{ $skill->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $skill->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $skill->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            @if(!$skill->delflg)
            <form method="POST" action="/admin/skills/{{ $skill->pkey }}/delete" class="d-inline ml-2">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        data-toggle="confirmation"
                        data-title="Delete this skill?"
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

@endsection