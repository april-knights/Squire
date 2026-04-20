@extends('layouts.app')
@section('title', 'Admin — Edit ' . $profile->secname)
@section('full_width', true)

@push('styles')
<style>
.card { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.card-header { background-color: rgba(0,0,0,0.3); border-bottom: 1px solid #8b3a3a; color: #efefef; font-weight: 600; }
.card-body { color: #efefef; }
.col-form-label { color: #efefef; }
.form-control { background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef; }
.form-control:focus { background-color: rgba(0,0,0,0.4); border-color: #c9a0a0; color: #efefef; box-shadow: 0 0 0 0.2rem rgba(139,58,58,0.4); }
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.flag-group-title { color: #c9a0a0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; margin-top: 1rem; border-bottom: 1px solid #8b3a3a; padding-bottom: 0.25rem; }
.flag-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.5rem; }
.flag-check { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #efefef; }
.flag-check input[type=checkbox] { width: 1rem; height: 1rem; accent-color: #8b3a3a; }
.btn-group-flags { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/security">Security Profiles</a></li>
        <li class="breadcrumb-item"><a href="/admin/security/{{ $profile->pkey }}">{{ $profile->secname }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<h2 class="mb-3">Edit Profile — {{ $profile->secname }}</h2>

<form method="POST" action="/admin/security/{{ $profile->pkey }}/edit">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header">Identity</div>
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="secname">Profile Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('secname') is-invalid @enderror"
                           id="secname" name="secname"
                           value="{{ old('secname', $profile->secname) }}" required maxlength="30">
                    @error('secname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="secdescr">Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('secdescr') is-invalid @enderror"
                           id="secdescr" name="secdescr"
                           value="{{ old('secdescr', $profile->secdescr) }}" maxlength="255">
                    @error('secdescr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Permissions</div>
        <div class="card-body">
            <div class="btn-group-flags">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllFlags(true)">Check All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllFlags(false)">Uncheck All</button>
            </div>

            @foreach($flag_groups as $group => $flags)
                <div class="flag-group-title">{{ $group }}</div>
                <div class="flag-grid mb-2">
                    @foreach($flags as $flag)
                    <label class="flag-check">
                        <input type="checkbox" name="{{ $flag }}" value="1"
                               {{ old($flag, $profile->{$flag}) ? 'checked' : '' }}>
                        {{ $flag_labels[$flag] }}
                    </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/security/{{ $profile->pkey }}" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

<script>
function setAllFlags(checked) {
    document.querySelectorAll('input[type=checkbox]').forEach(function(cb) {
        cb.checked = checked;
    });
}
</script>

@endsection