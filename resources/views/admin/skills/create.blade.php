@extends('layouts.app')
@section('title', 'Admin — New Skill')
@section('full_width', true)

@push('styles')
<style>
.card { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.card-header { background-color: rgba(0,0,0,0.3); border-bottom: 1px solid #8b3a3a; color: #efefef; font-weight: 600; }
.card-body { color: #efefef; }
.col-form-label { color: #efefef; }
.form-control { background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef; }
.form-control:focus { background-color: rgba(0,0,0,0.4); border-color: #c9a0a0; color: #efefef; box-shadow: 0 0 0 0.2rem rgba(139,58,58,0.4); }
select.form-control option { background-color: #5a2424; color: #efefef; }
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
        <li class="breadcrumb-item"><a href="/admin/skills">Skills</a></li>
        <li class="breadcrumb-item active">New Skill</li>
    </ol>
</nav>

<h2 class="mb-3">New Skill</h2>

<form method="POST" action="/admin/skills">
    @csrf

    <div class="card mb-4">
        <div class="card-header">Skill Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="parentid">Parent Group</label>
                <div class="col-sm-5">
                    <select class="form-control @error('parentid') is-invalid @enderror" id="parentid" name="parentid">
                        <option value="">— None (create as a top-level group) —</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->pkey }}"
                                {{ old('parentid', $preselectedParent) == $p->pkey ? 'selected' : '' }}>
                                {{ $p->skillname }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Leave blank to create a new top-level group.</small>
                    @error('parentid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="skillname">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('skillname') is-invalid @enderror"
                           id="skillname" name="skillname" value="{{ old('skillname') }}" required maxlength="64">
                    @error('skillname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="skilldescr">Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('skilldescr') is-invalid @enderror"
                           id="skilldescr" name="skilldescr" value="{{ old('skilldescr') }}" maxlength="255">
                    @error('skilldescr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="public" name="public" value="1"
                               {{ old('public') ? 'checked' : '' }}>
                        <label class="form-check-label" for="public">Public — visible on knight profiles</label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Create Skill</button>
            <a href="/admin/skills" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

@endsection