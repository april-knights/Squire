@extends('layouts.app')
@section('title', 'Admin — Edit ' . $division->name)
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
.knight-search-wrapper { position: relative; }
.knight-search-dropdown {
    position: absolute;
    z-index: 100;
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.knight-result-item { padding: 0.4rem 0.75rem; cursor: pointer; color: #efefef; font-size: 0.875rem; }
.knight-result-item:hover { background-color: rgba(139,58,58,0.4); }
.alias-locked,
.alias-locked:disabled,
input[disabled].alias-locked {
    background-color: rgba(0,0,0,0.4) !important;
    color: #c9a0a0 !important;
    border: 1px solid #5a2424 !important;
    opacity: 1 !important;
}
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/divisions">Divisions</a></li>
        <li class="breadcrumb-item"><a href="/admin/divisions/{{ $division->pkey }}">{{ $division->name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<h2 class="mb-3">Edit Division — {{ $division->name }}</h2>

<form method="POST" action="/admin/divisions/{{ $division->pkey }}/edit">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header">Identity</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="name">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $division->name) }}" required maxlength="30">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Alias</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control alias-locked" value="{{ $division->divalias }}" disabled>
                    <small class="form-text text-muted">Alias is locked after creation.</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="divdescr">Description</label>
                <div class="col-sm-6">
                    <textarea class="form-control @error('divdescr') is-invalid @enderror"
                              id="divdescr" name="divdescr" rows="3" maxlength="500">{{ old('divdescr', $division->divdescr) }}</textarea>
                    @error('divdescr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="motto">Motto</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control @error('motto') is-invalid @enderror"
                           id="motto" name="motto" value="{{ old('motto', $division->motto) }}" maxlength="64">
                    @error('motto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="color">Color</label>
                <div class="col-sm-3">
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control form-control-sm mr-2"
                               id="colorPicker" style="width:3rem; height:2rem; padding:0.1rem; cursor:pointer;"
                               value="{{ old('color', $division->color ?? '#8b3a3a') }}">
                        <input type="text" class="form-control @error('color') is-invalid @enderror"
                               id="color" name="color" value="{{ old('color', $division->color) }}"
                               maxlength="15" placeholder="#rrggbb">
                    </div>
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Leadership</div>
        <div class="card-body">

            @foreach([
                ['divlead', 'Division Leader',   'lead', $leaderName],
                ['divsec1', 'Division Second 1', 'sec1', $sec1Name],
                ['divsec2', 'Division Second 2', 'sec2', $sec2Name],
            ] as [$field, $label, $suffix, $current])
            <div class="form-group row">
                <label class="col-sm-3 col-form-label">{{ $label }}</label>
                <div class="col-sm-5">
                    <div class="knight-search-wrapper">
                        <input type="text"
                               class="form-control form-control-sm knight-search-input"
                               data-suffix="{{ $suffix }}"
                               placeholder="Type 3+ characters…"
                               value="{{ $current ? $current->rname : '' }}"
                               autocomplete="off">
                        <div class="knight-search-dropdown" id="results_{{ $suffix }}"></div>
                    </div>
                    <input type="hidden" name="{{ $field }}" id="pkey_{{ $suffix }}"
                           value="{{ old($field, $current?->pkey) }}">
                    <div class="small text-muted mt-1" id="label_{{ $suffix }}">
                        @if($current) Selected: {{ $current->rname }}{{ $current->dname ? ' (' . $current->dname . ')' : '' }} @endif
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/divisions/{{ $division->pkey }}" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

<script>
(function() {
    var picker = document.getElementById('colorPicker');
    var text   = document.getElementById('color');
    picker.addEventListener('input', function() { text.value = this.value; });
    text.addEventListener('input', function() {
        if (/^#[0-9a-fA-F]{6}$/.test(this.value)) picker.value = this.value;
    });
})();

(function() {
    document.querySelectorAll('.knight-search-input').forEach(function($input) {
        var suffix   = $input.dataset.suffix;
        var $results = document.getElementById('results_' + suffix);
        var $pkey    = document.getElementById('pkey_' + suffix);
        var $label   = document.getElementById('label_' + suffix);
        var timer    = null;

        $input.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(timer);
            $pkey.value = '';
            $label.textContent = '';

            if (q.length < 3) {
                $results.style.display = 'none';
                $results.innerHTML = '';
                return;
            }

            timer = setTimeout(function() {
                fetch('/admin/knights/search?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        $results.innerHTML = '';
                        if (!data.length) {
                            $results.innerHTML = '<div class="knight-result-item text-muted">No results</div>';
                        } else {
                            data.forEach(function(k) {
                                var item = document.createElement('div');
                                item.className = 'knight-result-item';
                                item.textContent = k.rname + (k.dname ? ' (' + k.dname + ')' : '');
                                item.addEventListener('click', function() {
                                    $pkey.value        = k.pkey;
                                    $input.value       = k.rname;
                                    $label.textContent = 'Selected: ' + k.rname + (k.dname ? ' (' + k.dname + ')' : '');
                                    $results.style.display = 'none';
                                });
                                $results.appendChild(item);
                            });
                        }
                        $results.style.display = 'block';
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!$input.contains(e.target) && !$results.contains(e.target)) {
                $results.style.display = 'none';
            }
        });
    });
})();
</script>

@endsection