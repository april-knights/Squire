@extends('layouts.app')
@section('title', 'Admin — ' . $profile->secname)
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
.flag-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.4rem; }
.flag-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
.flag-on  { color: #4caf50; }
.flag-off { color: #6a1a1a; opacity: 0.5; }
.flag-group-title { color: #c9a0a0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; margin-top: 1rem; border-bottom: 1px solid #8b3a3a; padding-bottom: 0.25rem; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/security">Security Profiles</a></li>
        <li class="breadcrumb-item active">{{ $profile->secname }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        {{ $profile->secname }}
        <small class="text-muted" style="font-size:0.9rem;">pkey: {{ $profile->pkey }}</small>
        @if($profile->delflg)
            <span class="badge badge-danger">Deleted</span>
        @elseif(!$profile->activeflg)
            <span class="badge badge-warning">Inactive</span>
        @else
            <span class="badge badge-success">Active</span>
        @endif
    </h2>
    <a href="/admin/security/{{ $profile->pkey }}/edit" class="btn btn-secondary btn-sm">Edit</a>
</div>

{{-- Details --}}
<div class="card mb-3">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $profile->secdescr ?? '—' }}</dd>
            <dt class="col-sm-3">Knights Assigned</dt>
            <dd class="col-sm-9">
                @if($knight_count > 0)
                    <a href="/admin/knights?security={{ $profile->pkey }}">{{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}</a>
                @else
                    <span class="text-muted">None</span>
                @endif
            </dd>
            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $profile->crtsetdt ?? '—' }}{{ $crtsetid_name ? ' by ' . $crtsetid_name : '' }}</dd>
            <dt class="col-sm-3">Last Modified</dt>
            <dd class="col-sm-9">{{ $profile->lstmdts ?? '—' }}{{ $lstmdby_name ? ' by ' . $lstmdby_name : '' }}</dd>
        </dl>
    </div>
</div>

{{-- Permission flag grid --}}
<div class="card mb-3">
    <div class="card-header">Permissions</div>
    <div class="card-body">
        @foreach($flag_groups as $group => $flags)
            <div class="flag-group-title">{{ $group }}</div>
            <div class="flag-grid mb-2">
                @foreach($flags as $flag)
                <div class="flag-item">
                    @if($profile->{$flag})
                        <i class="fas fa-check-circle flag-on"></i>
                    @else
                        <i class="fas fa-times-circle flag-off"></i>
                    @endif
                    <span>{{ $flag_labels[$flag] }}</span>
                </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

{{-- Status controls --}}
<div class="card mb-3" style="border-color: #7a6a00;">
    <div class="card-header" style="color: #c8a000;">Status Controls</div>
    <div class="card-body">
        <p class="text-muted small">These actions take effect immediately and are logged.</p>
        <div class="d-flex">

            {{-- Toggle active --}}
            @if(!in_array($profile->pkey, [0, 1]))
            <form method="POST" action="/admin/security/{{ $profile->pkey }}/toggle" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm {{ $profile->activeflg ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        data-toggle="confirmation"
                        data-title="{{ $profile->activeflg ? 'Deactivate this profile?' : 'Activate this profile?' }}"
                        data-btn-ok-label="{{ $profile->activeflg ? 'Deactivate' : 'Activate' }}"
                        data-btn-ok-class="btn-{{ $profile->activeflg ? 'warning' : 'success' }}"
                        data-btn-cancel-label="Cancel">
                    {{ $profile->activeflg ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            {{-- Delete -- only if not a protected profile --}}
            @if(!$profile->delflg)
            <div class="ml-2">
                @if($knight_count > 0)
                    {{-- Reassignment required --}}
                    <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteModal">
                        Delete
                    </button>
                @else
                    <form method="POST" action="/admin/security/{{ $profile->pkey }}/delete" class="d-inline">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger"
                                data-toggle="confirmation"
                                data-title="Delete this profile?"
                                data-btn-ok-label="Delete"
                                data-btn-ok-class="btn-danger"
                                data-btn-cancel-label="Cancel">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
            @endif
            @else
                <p class="text-muted small mb-0">Protected profile — cannot be deactivated or deleted.</p>
            @endif

        </div>
    </div>
</div>

{{-- Reassignment modal --}}
@if($knight_count > 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #5a2424; color: #efefef; border: 1px solid #8b3a3a;">
            <div class="modal-header" style="border-bottom: 1px solid #8b3a3a;">
                <h5 class="modal-title">Delete Security Profile</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#efefef;">&times;</button>
            </div>
            <form method="POST" action="/admin/security/{{ $profile->pkey }}/delete">
                @csrf
                <div class="modal-body">
                    <p>This profile has <strong>{{ $knight_count }} knight{{ $knight_count !== 1 ? 's' : '' }}</strong> assigned. Select a replacement profile before deleting.</p>
                    <div class="form-group">
                        <label for="replacement_pkey">Reassign knights to:</label>
                        <select class="form-control" name="replacement_pkey" id="replacement_pkey" required
                                style="background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef;">
                            <option value="">— Select profile —</option>
                            @foreach(DB::table('security')->where('pkey', '!=', $profile->pkey)->where('delflg', 0)->orderBy('pkey')->get() as $p)
                                <option value="{{ $p->pkey }}">{{ $p->secname }}</option>
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