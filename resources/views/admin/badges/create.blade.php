{{--
    Shared styles and image picker JS used by both create and edit blades.
    CREATE BLADE — save as resources/views/admin/badges/create.blade.php
--}}
@extends('layouts.app')
@section('title', 'Admin — New Badge')
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
/* Image picker */
.img-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.5rem; max-height: 260px; overflow-y: auto; padding: 0.5rem; border: 1px solid #8b3a3a; border-radius: 4px; background: rgba(0,0,0,0.2); }
.img-picker-item { cursor: pointer; text-align: center; border: 2px solid transparent; border-radius: 4px; padding: 0.25rem; transition: border-color 0.15s; }
.img-picker-item:hover { border-color: #c9a0a0; }
.img-picker-item.selected { border-color: #4caf50; }
.img-picker-item img { width: 64px; height: 64px; object-fit: contain; display: block; margin: 0 auto; }
.img-picker-item span { font-size: 0.65rem; color: #c9a0a0; display: block; word-break: break-all; margin-top: 0.2rem; }
.img-picker-dir-title { color: #c9a0a0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; margin: 0.75rem 0 0.25rem; border-bottom: 1px solid #8b3a3a; padding-bottom: 0.2rem; }
.img-preview-wrap { text-align: center; }
.img-preview-wrap img { max-width: 100px; max-height: 100px; object-fit: contain; border: 1px solid #8b3a3a; border-radius: 4px; padding: 0.25rem; background: rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/badges">Badges</a></li>
        <li class="breadcrumb-item active">New Badge</li>
    </ol>
</nav>

<h2 class="mb-3">New Badge</h2>

<form method="POST" action="/admin/badges" enctype="multipart/form-data">
    @csrf

    <div class="card mb-3">
        <div class="card-header">Badge Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="bdg_title">Title</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('bdg_title') is-invalid @enderror"
                           id="bdg_title" name="bdg_title" value="{{ old('bdg_title') }}" required maxlength="255">
                    @error('bdg_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="typcd">Type</label>
                <div class="col-sm-4">
                    <select class="form-control @error('typcd') is-invalid @enderror" id="typcd" name="typcd" required>
                        <option value="">— Select type —</option>
                        @foreach($typecds as $t)
                            <option value="{{ $t }}" {{ old('typcd') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('typcd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="bdgdesc">Description</label>
                <div class="col-sm-6">
                    <textarea class="form-control @error('bdgdesc') is-invalid @enderror"
                              id="bdgdesc" name="bdgdesc" rows="3" maxlength="500">{{ old('bdgdesc') }}</textarea>
                    @error('bdgdesc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="orderid">Order ID</label>
                <div class="col-sm-3">
                    <input type="number" class="form-control @error('orderid') is-invalid @enderror"
                           id="orderid" name="orderid" value="{{ old('orderid') }}" required>
                    <small class="form-text text-muted">Controls display order — lower appears first.</small>
                    <small class="form-text" id="orderid-hint" style="color:#c8a000;"></small>
                    @error('orderid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="roleid">Discord Role ID</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control @error('roleid') is-invalid @enderror"
                           id="roleid" name="roleid" value="{{ old('roleid') }}" maxlength="25">
                    <small class="form-text text-muted">Optional — links badge to a Discord role.</small>
                    @error('roleid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    {{-- Image section --}}
    <div class="card mb-4">
        <div class="card-header">Badge Image</div>
        <div class="card-body">
            <div class="row">
                {{-- Preview --}}
                <div class="col-md-2">
                    <div class="img-preview-wrap mb-2">
                        <img id="imgPreview" src="{{ asset('static/img/badges/NoArtYet.jpg') }}" alt="Preview">
                    </div>
                    <div class="small text-muted text-center" id="imgPreviewLabel">No image selected</div>
                </div>

                <div class="col-md-10">
                    {{-- Tab nav --}}
                    <ul class="nav nav-tabs mb-3" id="imgTabs" style="border-color: #8b3a3a;">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-pick" href="#" data-tab="pick"
                               style="color:#efefef; border-color:#8b3a3a #8b3a3a transparent;">Pick Existing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-upload" href="#" data-tab="upload"
                               style="color:#c9a0a0;">Upload New</a>
                        </li>
                    </ul>

                    {{-- Pick existing --}}
                    <div id="panel-pick">
                        <input type="hidden" name="imgurl" id="imgurl" value="{{ old('imgurl') }}">
                        @foreach($imgFiles as $dir => $files)
                            <div class="img-picker-dir-title">{{ $dir }}</div>
                            <div class="img-picker-grid">
                                @foreach($files as $path)
                                <div class="img-picker-item" data-path="{{ $path }}">
                                    <img src="{{ asset($path) }}" alt="{{ basename($path) }}">
                                    <span>{{ basename($path) }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    {{-- Upload new --}}
                    <div id="panel-upload" style="display:none;">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="img_subdir">Subdirectory</label>
                            <div class="col-sm-5">
                                <select class="form-control" id="img_subdir_select">
                                    @foreach($imgDirs as $dir)
                                        <option value="{{ $dir }}">{{ $dir }}</option>
                                    @endforeach
                                    <option value="__new__">+ New subdirectory…</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="img_subdir_new"
                                       name="img_subdir" placeholder="New subdirectory name"
                                       value="{{ $imgDirs[0] ?? 'misc' }}" style="display:none;">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="new_image">Image File</label>
                            <div class="col-sm-5">
                                <input type="file" class="form-control @error('new_image') is-invalid @enderror"
                                       id="new_image" name="new_image" accept="image/*">
                                <small class="form-text text-muted">PNG, JPG, GIF, WebP — max 2MB.</small>
                                @error('new_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Create Badge</button>
            <a href="/admin/badges" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

<script>
(function () {
    // Tab switching
    document.querySelectorAll('#imgTabs .nav-link').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('#imgTabs .nav-link').forEach(function (t) {
                t.classList.remove('active');
                t.style.borderBottomColor = 'transparent';
                t.style.color = '#c9a0a0';
            });
            this.classList.add('active');
            this.style.color = '#efefef';
            var panel = this.dataset.tab;
            document.getElementById('panel-pick').style.display   = panel === 'pick'   ? '' : 'none';
            document.getElementById('panel-upload').style.display = panel === 'upload' ? '' : 'none';
            // Clear upload input when switching to pick
            if (panel === 'pick') {
                document.getElementById('new_image').value = '';
            } else {
                document.getElementById('imgurl').value = '';
                document.querySelectorAll('.img-picker-item').forEach(function(i){ i.classList.remove('selected'); });
            }
        });
    });

    // Image picker
    document.querySelectorAll('.img-picker-item').forEach(function (item) {
        item.addEventListener('click', function () {
            document.querySelectorAll('.img-picker-item').forEach(function (i) { i.classList.remove('selected'); });
            this.classList.add('selected');
            var path = this.dataset.path;
            document.getElementById('imgurl').value = path;
            document.getElementById('imgPreview').src = '/' + path;
            document.getElementById('imgPreviewLabel').textContent = path.split('/').pop();
        });
    });

    // Upload preview
    document.getElementById('new_image').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('imgPreview').src = e.target.result;
                document.getElementById('imgPreviewLabel').textContent = 'New upload';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Subdir select — new subdir option
    document.getElementById('img_subdir_select').addEventListener('change', function () {
        var newInput = document.getElementById('img_subdir_new');
        if (this.value === '__new__') {
            newInput.style.display = '';
            newInput.value = '';
            newInput.focus();
        } else {
            newInput.style.display = 'none';
            newInput.value = this.value;
        }
    });
    // Init subdir hidden input
    var sel = document.getElementById('img_subdir_select');
    if (sel.value && sel.value !== '__new__') {
        document.getElementById('img_subdir_new').value = sel.value;
        document.getElementById('img_subdir_new').style.display = 'none';
    }
})();

// orderid range hints
    var rangeMap = {
        'position': { min: 1,   max: 50,  label: '1–50 (Position/pin-to-top)' },
        'gm':       { min: 51,  max: 100, label: '51–100 (Grandmaster series)' },
        'title':    { min: 151, max: 199, label: '151–199 (Title awards)' },
        'rank':     { min: 200, max: 299, label: '200–299 (Rank badges)' },
        'event':    { min: 500, max: null, label: '500+ (Event badges)' },
        'misc':     { min: 800, max: null, label: '800+ (Miscellaneous)' },
    };

    function updateOrderHint() {
        var typcd   = document.getElementById('typcd').value;
        var orderid = parseInt(document.getElementById('orderid').value, 10);
        var hint    = document.getElementById('orderid-hint');
        var range   = rangeMap[typcd];

        if (!range) {
            hint.textContent = '';
            return;
        }

        hint.textContent = 'Suggested range: ' + range.label;

        if (!isNaN(orderid)) {
            var inRange = orderid >= range.min && (range.max === null || orderid <= range.max);
            if (!inRange) {
                hint.textContent += ' ⚠ Current value is outside the expected range.';
                hint.style.color = '#e57373';
            } else {
                hint.style.color = '#c8a000';
            }
        }
    }

    document.getElementById('typcd').addEventListener('change', updateOrderHint);
    document.getElementById('orderid').addEventListener('input', updateOrderHint);
    updateOrderHint();
</script>

@endsection