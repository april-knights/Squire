@extends('layouts.app')

@section('title', 'New Election')

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
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
    <h4 style="margin:0;"><i class="fas fa-plus mr-2"></i>New Election</h4>
    <a href="{{ route('admin.elections.index') }}" style="color:#c0a0a0;font-size:0.85rem;">
        ← Back to Elections
    </a>
</div>

<div class="admin-card" style="max-width:480px;">
    <h5>Election Details</h5>
    <form method="POST" action="{{ route('admin.elections.store') }}">
        @csrf
        <div style="margin-bottom:0.75rem;">
            <label class="form-label-ea">Election Year</label>
            <input type="number" name="election_year" class="ea-input"
                value="{{ date('Y') }}"
                min="2024" max="2099" required>
        </div>
        <div style="margin-bottom:1rem;">
            <label class="form-label-ea">Notes (optional)</label>
            <textarea name="notes" class="ea-input" style="min-height:80px;resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn-admin">
            <i class="fas fa-plus mr-1"></i> Create Election
        </button>
        <a href="{{ route('admin.elections.index') }}" class="btn-admin"
            style="background:#5a2424;border-color:#8b3a3a;margin-left:0.5rem;">
            Cancel
        </a>
    </form>
</div>
@endsection