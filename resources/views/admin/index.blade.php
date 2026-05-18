@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('full_width', true)

@push('styles')
<style>
.admin-stat-card {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 1.25rem;
    text-align: center;
    margin-bottom: 1rem;
}
.admin-stat-card .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    color: #efefef;
}
.admin-stat-card .stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #c9a0a0;
    margin-top: 0.4rem;
}
.admin-section-card {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    display: block;
    color: #efefef;
    text-decoration: none;
    transition: background-color 0.15s, border-color 0.15s;
}
.admin-section-card:hover {
    background-color: rgba(139,58,58,0.35);
    border-color: #c9a0a0;
    color: #efefef;
    text-decoration: none;
}
.admin-section-card .section-icon {
    font-size: 1.75rem;
    color: #c9a0a0;
    margin-bottom: 0.5rem;
}
.admin-section-card .section-title {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}
.admin-section-card .section-desc {
    font-size: 0.8rem;
    color: #c9a0a0;
}
.breadcrumb {
    background-color: rgba(0,0,0,0.25);
    border: 1px solid #8b3a3a;
}
.breadcrumb-item.active { color: #c9a0a0; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Admin</li>
    </ol>
</nav>

<h2 class="mb-4">Admin Dashboard</h2>

{{-- Stats row --}}
<div class="row mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <div class="stat-number">{{ $stats['active'] }}</div>
            <div class="stat-label">Active Knights</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <div class="stat-number" style="color: #c8a000;">{{ $stats['inactive'] }}</div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <div class="stat-number" style="color: #8b2020;">{{ $stats['deleted'] }}</div>
            <div class="stat-label">Deleted</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <div class="stat-number" style="color: #2d6a2d;">{{ $stats['recent_logins'] }}</div>
            <div class="stat-label">Logins (30 days)</div>
        </div>
    </div>
</div>

{{-- Section links --}}
<div class="row">
    <div class="col-md-4">
        <a href="/admin/knights" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="section-title">Knights</div>
            <div class="section-desc">View, edit, activate, and delete knight accounts</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/security" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-lock"></i></div>
            <div class="section-title">Security Profiles</div>
            <div class="section-desc">Manage permission bit flags for each security level</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/ranks" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-medal"></i></div>
            <div class="section-title">Ranks</div>
            <div class="section-desc">Create, edit, and order knight ranks by rval</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/divisions" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-sitemap"></i></div>
            <div class="section-title">Divisions</div>
            <div class="section-desc">Manage divisions and their aliases</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/badges" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-certificate"></i></div>
            <div class="section-title">Badges</div>
            <div class="section-desc">Create and manage badge definitions and images</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/skills" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-tools"></i></div>
            <div class="section-title">Skills</div>
            <div class="section-desc">Manage skill library and group hierarchy</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/events" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="section-title">Events</div>
            <div class="section-desc">Create and manage April Knights events</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/links" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-link"></i></div>
            <div class="section-title">Links</div>
            <div class="section-desc">Manage links displayed on the Links page</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/images" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-images"></i></div>
            <div class="section-title">Images</div>
            <div class="section-desc">Browse and manage static image assets</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/elections" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-vote-yea"></i></div>
            <div class="section-title">Elections</div>
            <div class="section-desc">Manage Grandmaster elections, candidates, and results</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/oaths" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-scroll"></i></div>
            <div class="section-title">Oaths</div>
            <div class="section-desc">View and verify annual knight oaths</div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/admin/elections/settings" class="admin-section-card">
            <div class="section-icon"><i class="fas fa-cog"></i></div>
            <div class="section-title">Election Settings</div>
            <div class="section-desc">Configure oath thread, Reddit authorization, and bot settings</div>
        </a>
    </div>
</div>

@endsection