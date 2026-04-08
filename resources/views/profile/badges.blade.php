@extends('layouts.app')
@section('title', $knight->rname . ' - Badges')

@section('content')
<h1>Badges — {{ $knight->rname }}</h1>

@if($can_award_badges)
<form method="POST" action="/profile/{{ $knight->rname }}/badges">
    @csrf

    {{-- Add new badge --}}
    <div class="row">
        <div class="col-md-6">
            <h2>Award Badge</h2>
            <div class="form-group">
                <label>Badge</label>
                <select class="form-control" name="add_badge">
                    <option value="">— Select Badge —</option>
                    @foreach($all_badges as $badge)
                    <option value="{{ $badge->pkey }}">{{ $badge->bdg_title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="reason">Reason (optional)</label>
                <input class="form-control" type="text" name="reason[0]" maxlength="500">
            </div>
        </div>
    </div>

    {{-- Current badges --}}
    <div class="row">
        <div class="col">
            <h2>Current Badges</h2>
            @forelse($knight_badges as $kb)
            <div class="row mb-2 align-items-center">
                <div class="col-auto">
                    <img src="{{ asset($kb->imgurl ?? 'static/img/badges/NoArtYet.jpg') }}"
                        width="64" alt="{{ $kb->bdg_title }}" title="{{ $kb->bdg_title }}">
                </div>
                <div class="col">
                    <strong>{{ $kb->bdg_title }}</strong>
                    @if($kb->pivot->bdgreason)
                    <br><small>{{ $kb->pivot->bdgreason }}</small>
                    @endif
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured[]"
                            value="{{ $kb->pivot->pkey }}"
                            @if($kb->pivot->featured) checked @endif>
                        <label class="form-check-label">Featured</label>
                    </div>
                </div>
                @if($can_award_badges)
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove[]"
                            value="{{ $kb->pivot->pkey }}">
                        <label class="form-check-label text-danger">Remove</label>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <p>No badges awarded yet.</p>
            @endforelse
        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <a href="/profile/{{ $knight->rname }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success float-right">Save</button>
        </div>
    </div>
</form>
@else
{{-- Read only view for self with featured toggle only --}}
<form method="POST" action="/profile/{{ $knight->rname }}/badges">
    @csrf
    <div class="row">
        <div class="col">
            <h2>Your Badges</h2>
            @forelse($knight_badges as $kb)
            <div class="row mb-2 align-items-center">
                <div class="col-auto">
                    <img src="{{ asset($kb->imgurl ?? 'static/img/badges/NoArtYet.jpg') }}"
                        width="64" alt="{{ $kb->bdg_title }}" title="{{ $kb->bdg_title }}">
                </div>
                <div class="col">
                    <strong>{{ $kb->bdg_title }}</strong>
                    <span class="text-muted">({{ $kb->typcd }})</span>
                    @if($kb->pivot->bdgreason)
                    <br><small>{{ $kb->pivot->bdgreason }}</small>
                    @endif
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured[]"
                            value="{{ $kb->pivot->pkey }}"
                            @if($kb->pivot->featured) checked @endif>
                        <label class="form-check-label">Featured</label>
                    </div>
                </div>
            </div>
            @empty
            <p>No badges awarded yet.</p>
            @endforelse
        </div>
    </div>
    <div class="row mt-3">
        <div class="col">
            <a href="/profile/{{ $knight->rname }}" class="btn btn-secondary">Cancel</a>
            @if($editing_self)
            <button type="submit" class="btn btn-success float-right">Save Featured</button>
            @endif
        </div>
    </div>
</form>
@endif
@endsection
