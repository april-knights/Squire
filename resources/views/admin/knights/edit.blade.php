@extends('layouts.app')
@section('title', 'Admin — Edit ' . $knight->rname)
@section('content')
@section('full_width', true)

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/knights">Knights</a></li>
        <li class="breadcrumb-item"><a href="/admin/knights/{{ $knight->pkey }}">{{ $knight->rname }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<h2>Edit Knight — {{ $knight->rname }}</h2>

<form method="POST" action="/admin/knights/{{ $knight->pkey }}/edit">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header">Identity</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="rname">Reddit Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('rname') is-invalid @enderror"
                           id="rname" name="rname" value="{{ old('rname', $knight->rname) }}" required>
                    @error('rname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="dname">Discord Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('dname') is-invalid @enderror"
                           id="dname" name="dname" value="{{ old('dname', $knight->dname) }}">
                    @error('dname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="discordid">Discord ID</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('discordid') is-invalid @enderror"
                           id="discordid" name="discordid" value="{{ old('discordid', $knight->discordid) }}">
                    @error('discordid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">Email</label>
                <div class="col-sm-6">
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email', $knight->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Role &amp; Assignment</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="batt">Battalion</label>
                <div class="col-sm-6">
                    <select class="form-control" id="batt" name="batt">
                        <option value="">— None —</option>
                        @foreach ($battalions as $b)
                            <option value="{{ $b->pkey }}" {{ old('batt', $knight->batt) == $b->pkey ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="rnk">Rank</label>
                <div class="col-sm-6">
                    <select class="form-control" id="rnk" name="rnk">
                        <option value="">— None —</option>
                        @foreach ($ranks as $r)
                            <option value="{{ $r->pkey }}" {{ old('rnk', $knight->rnk) == $r->pkey ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="security">Security Profile</label>
                <div class="col-sm-6">
                    <select class="form-control" id="security" name="security">
                        <option value="">— None —</option>
                        @foreach ($securities as $s)
                            <option value="{{ $s->pkey }}" {{ old('security', $knight->security) == $s->pkey ? 'selected' : '' }}>
                                {{ $s->secname }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Admin Notes</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="inttrans">Internal Transfer Note</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('inttrans') is-invalid @enderror"
                           id="inttrans" name="inttrans" value="{{ old('inttrans', $knight->inttrans) }}">
                    @error('inttrans')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="onote">Officer Note</label>
                <div class="col-sm-6">
                    <textarea class="form-control @error('onote') is-invalid @enderror"
                              id="onote" name="onote" rows="3">{{ old('onote', $knight->onote) }}</textarea>
                    @error('onote')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/knights/{{ $knight->pkey }}" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

@endsection