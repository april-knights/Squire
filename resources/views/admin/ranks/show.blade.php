@extends('layouts.app')
@section('title', 'Admin — ' . $rank->name)
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
        <li class="breadcrumb-item"><a href="/admin/ranks">Ranks</a></li>
        <li class="breadcrumb-item active">{{ $rank->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $rank->name }}
        @if($rank->delflg)
            <span class="badge badge-danger">Deleted</span>
        @elseif(!$rank->activeflg)
            <span class="badge badge-warning">Inactive</span>
        @else
            <span class="badge badge-success">Active</span>
        @endif
    </h2>
    <a href="/admin/ranks/{{ $rank->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $rank->name }}</dd>

            <dt class="col-sm-3">Rank Value (rval)</dt>
            <dd class="col-sm-9">{{ $rank->rval }} <small class="text-muted">(lower = higher rank)</small></dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $rank->rankdescr ?? '—' }}</dd>

            <dt class="col-sm-3">Unique</dt>
            <dd class="col-sm-9">
                @if($rank->uniqe)
                    <i class="fas fa-check text-success"></i> Yes — only one knight may hold this rank
                @else
                    No
                @endif
            </dd>

            <dt class="col-sm-3">Knights Assigned</dt>
            <dd class="col-sm-9">
                @if($knight_count > 0)
                    <a href="/admin/knights?rnk={{ $rank->pkey }}">{{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}</a>
                @else
                    <span class="text-muted">None</span>
                @endif
            </dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $rank->crtsetdt ?? '—' }}{{ $crtsetid_name ? ' by ' . $crtsetid_name : '' }}</dd>

            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">{{ $rank->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
        </dl>
    </div>
</div>

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">

            {{-- Toggle active --}}
            <form method="POST" action="/admin/ranks/{{ $rank->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $rank->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $rank->activeflg ? 'Deactivate this rank?' : 'Activate this rank?' }}"
                        data-btn-ok-label="{{ $rank->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $rank->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $rank->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            {{-- Delete --}}
            @if(!$rank->delflg)
            <div class="ml-2">
                @if($knight_count > 0)
                    <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteModal">
                        Delete
                    </button>
                @else
                    <form method="POST" action="/admin/ranks/{{ $rank->pkey }}/delete" class="d-inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger"
                                data-toggle="confirmation"
                                data-title="Delete this rank?"
                                data-btn-ok-label="Delete"
                                data-btn-ok-class="btn-danger"
                                data-btn-cancel-label="Cancel">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

{{-- Reassignment modal --}}
@if($knight_count > 0 && !$rank->delflg)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #5a2424; color: #efefef; border: 1px solid #8b3a3a;">
            <div class="modal-header" style="border-bottom: 1px solid #8b3a3a;">
                <h5 class="modal-title">Delete Rank</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#efefef;">&times;</button>
            </div>
            <form method="POST" action="/admin/ranks/{{ $rank->pkey }}/delete">
                @csrf
                <div class="modal-body">
                    <p>This rank has <strong>{{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}</strong> assigned. Select a replacement rank before deleting.</p>
                    <div class="form-group">
                        <label for="replacement_pkey">Reassign knights to:</label>
                        <select class="form-control" name="replacement_pkey" id="replacement_pkey" required
                                style="background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef;">
                            <option value="">— Select rank —</option>
                            @foreach(DB::table('krank')->where('pkey', '!=', $rank->pkey)->where('delflg', 0)->orderBy('rval')->get() as $r)
                                <option value="{{ $r->pkey }}">{{ $r->name }} (rval: {{ $r->rval }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #8b3a3a;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reassign &amp; Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection