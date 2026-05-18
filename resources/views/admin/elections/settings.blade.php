@extends('layouts.app')

@section('title', 'Election Settings')

@section('content')
<style>
.admin-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.admin-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.form-label-ea {
    font-size: 0.8rem;
    color: #c0a0a0;
    display: block;
    margin-bottom: 0.3rem;
}
.ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    width: 100%;
}
.ea-input:focus {
    outline: none;
    border-color: #efefef;
}
.btn-admin {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.4rem 1rem;
    border-radius: 4px;
    font-size: 0.88rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.15s ease;
}
.btn-admin:hover {
    background-color: #a04040;
    color: #fff;
    text-decoration: none;
}
.status-ok   { color: #5cb85c; }
.status-warn { color: #f0ad4e; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;"><i class="fas fa-cog mr-2"></i>Election Settings</h4>
    <a href="{{ route('admin.elections.index') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Elections
    </a>
</div>

{{-- Reddit Authorization --}}
<div class="admin-card">
    <h5><i class="fas fa-reddit mr-1"></i> AKSquire2 Reddit Authorization</h5>
    @if($redditAuthorized)
    <p style="color:#5cb85c;font-size:0.88rem;margin-bottom:0.75rem;">
        <i class="fas fa-check-circle mr-1"></i> AKSquire2 is authorized and ready to post.
    </p>
    @else
    <p style="color:#f0ad4e;font-size:0.88rem;margin-bottom:0.75rem;">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        AKSquire2 is not authorized. Posting nomination and debate threads will not work until authorized.
    </p>
    @endif
    <a href="{{ route('admin.elections.reddit-auth') }}" class="btn-admin">
        <i class="fas fa-link mr-1"></i>
        {{ $redditAuthorized ? 'Re-authorize AKSquire2' : 'Authorize AKSquire2' }}
    </a>
</div>

{{-- Oath Thread Settings --}}
<div class="admin-card" style="max-width:560px;">
    <h5><i class="fas fa-scroll mr-1"></i> Annual Oath Thread</h5>
    <form method="POST" action="{{ route('admin.elections.settings.update') }}">
        @csrf
        <div style="margin-bottom:0.75rem;">
            <label class="form-label-ea">Oath Thread URL</label>
            <input type="url" name="oath_thread_url" class="ea-input"
                value="{{ $oathThreadUrl }}"
                placeholder="https://www.reddit.com/r/AprilKnights/comments/...">
        </div>
        <button type="submit" class="btn-admin">
            <i class="fas fa-save mr-1"></i> Save Settings
        </button>
    </form>
</div>
@endsection