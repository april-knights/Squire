{{-- CREATE BLADE — save as resources/views/admin/events/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Admin — New Event')
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
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
        <li class="breadcrumb-item"><a href="/admin/events">Events</a></li>
        <li class="breadcrumb-item active">New Event</li>
    </ol>
</nav>

<h2 class="mb-3">New Event</h2>

<form method="POST" action="/admin/events">
    @csrf

    <div class="card mb-4">
        <div class="card-header">Event Details</div>
        <div class="card-body">

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="title">Title</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}" required maxlength="30">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="eventdescr">Description</label>
                <div class="col-sm-6">
                    <textarea class="form-control @error('eventdescr') is-invalid @enderror"
                              id="eventdescr" name="eventdescr" rows="3" maxlength="500">{{ old('eventdescr') }}</textarea>
                    @error('eventdescr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="organizer">Organizer</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control @error('organizer') is-invalid @enderror"
                           id="organizer" name="organizer" value="{{ old('organizer') }}" maxlength="30">
                    @error('organizer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="livedate">Live Date</label>
                <div class="col-sm-3">
                    <input type="date" class="form-control @error('livedate') is-invalid @enderror"
                           id="livedate" name="livedate" value="{{ old('livedate') }}">
                    <small class="form-text text-muted">Leave blank if unknown.</small>
                    @error('livedate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="enddate">End Date</label>
                <div class="col-sm-3">
                    <input type="date" class="form-control @error('enddate') is-invalid @enderror"
                           id="enddate" name="enddate" value="{{ old('enddate') }}">
                    <small class="form-text text-muted">Leave blank if unknown.</small>
                    @error('enddate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="eventtype">Type</label>
                <div class="col-sm-3">
                    <select class="form-control @error('eventtype') is-invalid @enderror" id="eventtype" name="eventtype" required>
                        @foreach($eventTypes as $t)
                            <option value="{{ $t }}" {{ old('eventtype', 'reddit') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('eventtype')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="profileflg" name="profileflg" value="1"
                               {{ old('profileflg', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="profileflg">
                            Show on profiles — selectable as a knight's first event
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="/admin/events" class="btn btn-outline-secondary ml-2">Cancel</a>
        </div>
    </div>

</form>

@endsection