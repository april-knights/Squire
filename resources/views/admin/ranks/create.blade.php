{{--
    CREATE BLADE — save as resources/views/admin/ranks/create.blade.php
--}}
@extends('layouts.app')
@section('title', 'Admin — New Rank')
@section('full_width', true)

@push('styles')
<style>
.card { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.card-header { background-color: rgba(0,0,0,0.3); border-bottom: 1px solid #8b3a3a; color: #efefef; font-weight: 600; }
.card-body { color: #efefef; }
.col-form-label { color: #efefef; }
.form-control { background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef; }
.form-control:focus { background-color: rgba(0,0,0,0.4); border-color: #c9a0a0; color: #efefef; box-shadow: 0 0 0 0.2rem rgba(139,58,58,0.4); }
.form-check-label { color: #efefef; }
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
        <li class="breadcrumb-item active">New Rank</li>
    </ol>
</nav>

<h2 class="mb-3">New Rank</h2>

<form method="POST" action="/admin/ranks">
    @csrf

    <div class="card mb-4">
        <div class="card-header">Rank Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="name">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required maxlength="20">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="rval">Rank Value (rval)</label>
                <div class="col-sm-3">
                    <input type="number" class="form-control @error('rval') is-invalid @enderror"
                           id="rval" name="rval" value="{{ old('rval') }}" required min="1" max="99">
                    <small class="form-text text-muted">Lower = higher rank. 1 = Grandmaster, 99 = Applicant.</small>
                    @error('rval')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="rankdescr">Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('rankdescr') is-invalid @enderror"
                           id="rankdescr" name="rankdescr" value="{{ old('rankdescr') }}" maxlength="255">
                    @error('rankdescr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="uniqe" name="uniqe" value="1"
                               {{ old('uniqe') ? 'checked' : '' }}>
                        <label class="form-check-label" for="uniqe">Unique — only one knight may hold this rank</label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Create Rank</button>
            <a href="/admin/ranks" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

@endsection