@extends('layouts.app')
@section('title', 'Admin — Skills')
@section('full_width', true)

@push('styles')
<style>
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
.skill-group {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    margin-bottom: 1rem;
}
.skill-group-header {
    background-color: rgba(0,0,0,0.3);
    border-bottom: 1px solid #8b3a3a;
    padding: 0.6rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.skill-group-header .group-name {
    font-weight: 600;
    color: #efefef;
    font-size: 1rem;
}
.skill-group-header .group-actions { display: flex; gap: 0.5rem; align-items: center; }
.skill-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 1rem 0.4rem 2rem;
    border-bottom: 1px solid rgba(139,58,58,0.2);
    color: #efefef;
    font-size: 0.9rem;
}
.skill-row:last-child { border-bottom: none; }
.skill-row.row-inactive { opacity: 0.6; }
.skill-row.row-deleted { opacity: 0.4; text-decoration: line-through; }
.skill-row .skill-name a { color: #efefef; }
.skill-row .skill-meta { color: #c9a0a0; font-size: 0.78rem; }
.skill-row .skill-actions { display: flex; gap: 0.4rem; align-items: center; }
.badge-active   { background-color: #2d6a2d; color: #fff; font-size: 0.7rem; }
.badge-inactive { background-color: #7a6a00; color: #fff; font-size: 0.7rem; }
.badge-deleted  { background-color: #6a1a1a; color: #fff; font-size: 0.7rem; }
.no-children { padding: 0.5rem 1rem 0.5rem 2rem; color: #c9a0a0; font-size: 0.8rem; font-style: italic; }
.public-badge { font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 3px; background-color: rgba(45,106,45,0.3); color: #4caf50; border: 1px solid #2d6a2d; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item active">Skills</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Skill Library</h2>
    <div>
        <a href="/admin/skills/create" class="btn btn-outline-secondary btn-sm mr-2">+ New Group</a>
        <a href="/admin/skills/create?parent=1" class="btn btn-primary btn-sm">+ New Skill</a>
    </div>
</div>

@foreach($parents as $parent)
@php
    $parentChildren = $children->where('parentid', $parent->pkey);
    $isDeleted  = (bool)$parent->delflg;
    $isInactive = !(bool)$parent->activeflg && !$isDeleted;
    $parentCount = $counts[$parent->pkey] ?? 0;
@endphp
<div class="skill-group">
    <div class="skill-group-header">
        <div class="group-name">
            <a href="/admin/skills/{{ $parent->pkey }}" style="color:#efefef;">{{ $parent->skillname }}</a>
            @if($parent->public) <span class="public-badge ml-1">public</span> @endif
            @if($isDeleted)
                <span class="badge badge-deleted ml-1">Deleted</span>
            @elseif($isInactive)
                <span class="badge badge-inactive ml-1">Inactive</span>
            @endif
            <small class="text-muted ml-2" style="font-size:0.75rem;">{{ $parentChildren->count() }} skill(s)</small>
        </div>
        <div class="group-actions">
            <a href="/admin/skills/create?parent={{ $parent->pkey }}" class="btn btn-sm btn-outline-secondary">+ Add Skill</a>
            <a href="/admin/skills/{{ $parent->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
    </div>

    @forelse($parentChildren->sortBy('skillname') as $skill)
    @php
        $sDeleted  = (bool)$skill->delflg;
        $sInactive = !(bool)$skill->activeflg && !$sDeleted;
        $sClass    = $sDeleted ? 'row-deleted' : ($sInactive ? 'row-inactive' : '');
        $sCount    = $counts[$skill->pkey] ?? 0;
    @endphp
    <div class="skill-row {{ $sClass }}">
        <div class="skill-name">
            <a href="/admin/skills/{{ $skill->pkey }}">{{ $skill->skillname }}</a>
            @if($skill->public) <span class="public-badge ml-1">public</span> @endif
            @if($sDeleted)
                <span class="badge badge-deleted ml-1">Deleted</span>
            @elseif($sInactive)
                <span class="badge badge-inactive ml-1">Inactive</span>
            @endif
            @if($skill->skilldescr)
                <span class="skill-meta ml-2">— {{ Str::limit($skill->skilldescr, 60) }}</span>
            @endif
        </div>
        <div class="skill-actions">
            @if($sCount > 0)
                <span class="text-muted small">{{ $sCount }} knight(s)</span>
            @endif
            <a href="/admin/skills/{{ $skill->pkey }}/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
    </div>
    @empty
    <div class="no-children">No skills in this group yet.</div>
    @endforelse
</div>
@endforeach

{{-- Orphaned skills (parentid set but parent doesn't exist or is deleted) --}}
@php
    $parentPkeys = $parents->pluck('pkey')->toArray();
    $orphans = $children->filter(fn($s) => !in_array($s->parentid, $parentPkeys));
@endphp
@if($orphans->count())
<div class="skill-group" style="border-color: #c8a000;">
    <div class="skill-group-header" style="border-color: #c8a000;">
        <div class="group-name" style="color: #c8a000;">⚠ Orphaned Skills</div>
    </div>
    @foreach($orphans as $skill)
    <div class="skill-row">
        <div class="skill-name">
            <a href="/admin/skills/{{ $skill->pkey }}">{{ $skill->skillname }}</a>
            <span class="skill-meta ml-2">parentid: {{ $skill->parentid }}</span>
        </div>
        <div class="skill-actions">
            <a href="/admin/skills/{{ $skill->pkey }}/edit" class="btn btn-sm btn-outline-warning">Fix</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection