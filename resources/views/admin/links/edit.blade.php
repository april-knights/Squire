@extends('layouts.app')
@section('title', 'Admin — Edit ' . $link->linknm)
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
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.img-preview { max-width: 64px; max-height: 64px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.2rem; background: rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/links">Links</a></li>
        <li class="breadcrumb-item"><a href="/admin/links/{{ $link->pkey }}">{{ $link->linknm }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<h2 class="mb-3">Edit Link — {{ $link->linknm }}</h2>

<form method="POST" action="/admin/links/{{ $link->pkey }}/edit">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header">Link Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="typcd">Type</label>
                <div class="col-sm-3">
                    <select class="form-control @error('typcd') is-invalid @enderror" id="typcd" name="typcd" required>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ old('typcd', $link->typcd) === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('typcd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="orderid">Order</label>
                <div class="col-sm-2">
                    <input type="number" class="form-control @error('orderid') is-invalid @enderror"
                        id="orderid" name="orderid" value="{{ old('orderid', $link->orderid ?? 0) }}">
                    <small class="form-text text-muted">Display order within type group.</small>
                    @error('orderid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="linknm">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('linknm') is-invalid @enderror"
                           id="linknm" name="linknm"
                           value="{{ old('linknm', $link->linknm) }}" required maxlength="50">
                    @error('linknm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="linkdesc">Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('linkdesc') is-invalid @enderror"
                           id="linkdesc" name="linkdesc"
                           value="{{ old('linkdesc', $link->linkdesc) }}" required maxlength="255">
                    @error('linkdesc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="linkurl">URL</label>
                <div class="col-sm-6">
                    <input type="url" class="form-control @error('linkurl') is-invalid @enderror"
                           id="linkurl" name="linkurl"
                           value="{{ old('linkurl', trim($link->linkurl)) }}" maxlength="150">
                    @error('linkurl')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="imgurl">Image URL</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('imgurl') is-invalid @enderror"
                           id="imgurl" name="imgurl"
                           value="{{ old('imgurl', $link->imgurl) }}" maxlength="150"
                           placeholder="/static/img/example.png">
                    <small class="form-text text-muted">Path to image — e.g. /static/img/BackgroundLogo.png</small>
                    @error('imgurl')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-1">
                    @if($link->imgurl)
                        <img id="imgPreview" class="img-preview" src="{{ $link->imgurl }}" alt="Preview">
                    @else
                        <img id="imgPreview" class="img-preview" src="" alt="Preview" style="display:none;">
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/links/{{ $link->pkey }}" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

<script>
document.getElementById('imgurl').addEventListener('input', function () {
    var preview = document.getElementById('imgPreview');
    var val = this.value.trim();
    if (val) {
        preview.src = val;
        preview.style.display = 'block';
        preview.onerror = function() { preview.style.display = 'none'; };
    } else {
        preview.style.display = 'none';
    }
});
</script>

@endsection