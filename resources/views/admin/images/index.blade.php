@extends('layouts.app')
@section('title', 'Admin — Images')
@section('full_width', true)

@push('styles')
<style>
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.dir-section { margin-bottom: 2rem; }
.dir-header {
    color: #c9a0a0;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #8b3a3a;
    padding-bottom: 0.3rem;
    margin-bottom: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.img-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 0.75rem;
}
.img-card {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.5rem;
    text-align: center;
    position: relative;
}
.img-card img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    display: block;
    margin: 0 auto 0.4rem;
}
.img-card .img-name {
    font-size: 0.65rem;
    color: #c9a0a0;
    word-break: break-all;
    line-height: 1.3;
}
.img-card .img-actions {
    margin-top: 0.4rem;
}
.img-card .btn-delete {
    font-size: 0.65rem;
    padding: 0.1rem 0.4rem;
    color: #e57373;
    border-color: #6a1a1a;
    background: transparent;
}
.img-card .btn-delete:hover { background-color: rgba(106,26,26,0.3); }
.dir-filter { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 1.5rem; }
.dir-filter a { font-size: 0.78rem; }
.form-control { background-color: rgba(0,0,0,0.3); border: 1px solid #8b3a3a; color: #efefef; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Images</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Image Management</h2>
    <a href="/admin/images/upload" class="btn btn-primary btn-sm">+ Upload Image</a>
</div>

{{-- Directory filter --}}
<div class="dir-filter">
    <a href="/admin/images" class="btn btn-sm {{ !$filter ? 'btn-secondary' : 'btn-outline-secondary' }}">All</a>
    @foreach($dirs as $dir)
        <a href="/admin/images?dir={{ urlencode($dir) }}"
           class="btn btn-sm {{ $filter === $dir ? 'btn-secondary' : 'btn-outline-secondary' }}">
            {{ $dir }}
        </a>
    @endforeach
</div>

{{-- Image grid by directory --}}
@forelse($structure as $dir => $files)
<div class="dir-section">
    <div class="dir-header">
        <span>{{ $dir }} <span class="text-muted">({{ count($files) }})</span></span>
    </div>
    <div class="img-grid">
        @foreach($files as $file)
        @php
            $relativePath = $dir === '(root)' ? $file : $dir . '/' . $file;
            $urlPath = '/static/img/' . $relativePath;
        @endphp
        <div class="img-card">
            <img src="{{ $urlPath }}" alt="{{ $file }}"
                 onerror="this.src='/static/img/badges/NoArtYet.jpg'">
            <div class="img-name">{{ $file }}</div>
            <div class="img-actions">
                <a href="/admin/images/delete?path={{ urlencode($relativePath) }}"
                   class="btn btn-delete btn-sm">Delete</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<p class="text-muted">No images found.</p>
@endforelse

@endsection