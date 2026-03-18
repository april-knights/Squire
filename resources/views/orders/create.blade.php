@extends('layouts.app')

@section('title', 'New Order')

@section('content')
<?php /** @var array $levels */ ?>
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<h1>New Order</h1>
<form method="POST" id="create" action="/orders">
    @csrf
    <div class="row">
        <div class="col-md">
            <div class="form-group">
                <label for="title">Title</label>
                <input class="form-control" id="title" name="title" type="text"
                    value="{{ old('title') }}" maxlength="255">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="level">Order Level</label>
                <select class="form-control" id="level" name="level" onchange="toggleBattalion(this.value)">
                    @foreach ($levels as $value => $label)
                    <option value="{{ $value }}" {{ old('level') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row" id="battalion_row" style="display:none;">
        <div class="col-md">
            <div class="form-group">
                <label for="fkeybattalion">Battalion</label>
                <select class="form-control" id="fkeybattalion" name="fkeybattalion">
                    <option value="">— None —</option>
                    @php /** @var \App\Model\Battalion $battalion */ @endphp
                    @foreach ($battalions as $battalion)
                    <option value="{{ $battalion->pkey }}"
                        {{ old('fkeybattalion') == $battalion->pkey ? 'selected' : '' }}>
                        {{ $battalion->name }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    Only visible to members of this battalion and Grandmaster/Admin.
                </small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="body">Order</label>
                <textarea class="form-control" id="body" name="body">{{ old('body') }}</textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a href="/orders" class="btn btn-secondary">Cancel</a>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-success float-right">Post Order</button>
        </div>
    </div>
</form>

<script src="https://cdn.tiny.cloud/1/ozf1zy8pckfc77ph8goobxjmzt8l08xc87pkj1o4hb9yndh0/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#body',
        plugins: 'lists link code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
        menubar: false,
        block_unsupported_drop: true,
        paste_block_drop: false,
        images_upload_handler: function(blobInfo, progress) {
            return Promise.reject('Image uploads are not supported.');
        },
        setup: function(editor) {
            editor.on('init', function() {
                // If level is battalion on page load, show battalion row
                toggleBattalion(document.getElementById('level').value);
            });
        }
    });

    function toggleBattalion(value) {
        var row = document.getElementById('battalion_row');
        if (value == 0) {
            row.style.display = '';
            // If only one battalion available, auto-select it
            var select = document.getElementById('fkeybattalion');
            if (select.options.length == 2) {
                select.selectedIndex = 1;
            }
        } else {
            row.style.display = 'none';
        }
    }
</script>
@endsection
