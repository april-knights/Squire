@extends('layouts.app')
@section('title', 'Admin — Upload Image')
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
.img-preview { max-width: 150px; max-height: 150px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.25rem; background: rgba(0,0,0,0.2); display: none; margin-top: 0.5rem; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/images">Images</a></li>
        <li class="breadcrumb-item active">Upload</li>
    </ol>
</nav>

<h2 class="mb-3">Upload Image</h2>

<form method="POST" action="/admin/images" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4">
        <div class="card-header">Upload Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="subdir">Directory</label>
                <div class="col-sm-5">
                    <select class="form-control" id="subdir" name="subdir">
                        @foreach($dirs as $dir)
                            <option value="{{ $dir === '(root)' ? '' : $dir }}">{{ $dir }}</option>
                        @endforeach
                        <option value="__new__">+ New directory…</option>
                    </select>
                    <input type="text" class="form-control mt-2" id="new_subdir" name="new_subdir"
                           placeholder="New directory name (letters, numbers, hyphens)"
                           style="display:none;">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="image">Image File</label>
                <div class="col-sm-5">
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                           id="image" name="image" accept="image/*" required>
                    <small class="form-text text-muted">PNG, JPG, GIF, WebP — max 4MB. Uploading a file with the same name as an existing file will overwrite it.</small>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <img id="imgPreview" class="img-preview" src="" alt="Preview">
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Upload</button>
            <a href="/admin/images" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

<script>
document.getElementById('subdir').addEventListener('change', function () {
    var newInput = document.getElementById('new_subdir');
    newInput.style.display = this.value === '__new__' ? '' : 'none';
    if (this.value !== '__new__') newInput.value = '';
});

document.getElementById('image').addEventListener('change', function () {
    var preview = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>

@endsection