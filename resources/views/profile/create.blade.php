@extends('layouts.app')

@section('title', 'Creating new Knight')

@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<h1>Create new Knight</h1>
<form method="POST">
    @csrf
    <div class="row">
        <div class="col-sm">
            <div class="form-group">
                <label for="knum">Knight Number</label>
                <input class="form-control" id="knum" name="knum" type="text" size="6"
                    placeholder="100000" pattern="\d{6}" inputmode="numeric" required>
                </input>
            </div>
        </div>
        <div class="col-sm">
            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" id="email" name="email" type="email"></input>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            <div class="form-group">
                <label for="rname">Reddit Name</label>
                <input class="form-control" id="rname" name="rname" type="text" required></input>
                <small id="rnameHelpBlock" class="form-text text-muted">
                    Without the /u/
                </small>
            </div>
        </div>
        <div class="col-sm">
            <div class="form-group">
                <label for="dname">Discord Name</label>
                <input class="form-control" id="dname" name="dname" type="text" pattern=".*#\d{4}"></input>
                <small id="dnameHelpBlock" class="form-text text-muted">
                    Format: Username#1234
                </small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md">
                    <div class="form-group">
                        <label>Battalion</label>
                        <select class="custom-select" name="batt">
                            @foreach ($all_batts as $batt)
                            <option value="{{ $batt->pkey }}" label="{{ $batt->name }}"
                                @if ($batt->pkey == $def_batt) selected @endif>
                                {{ $batt->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label>Rank</label>
                        <select class="custom-select" name="rank">
                            @foreach ($all_ranks as $rank)
                            <option value="{{ $rank->pkey }}" label="{{ $rank->name }}"
                                @if ($rank->pkey == $def_rank) selected @endif>
                                {{ $rank->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label>Security</label>
                        <select class="custom-select" name="security">
                            @foreach ($all_secs as $sec)
                            <option value="{{ $sec->pkey }}" label="{{ $sec->secname }}"
                                @if ($sec->pkey == $def_sec) selected @endif>
                                {{ $sec->secname }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Divisions</label>
                        <fieldset name="divs">
                            @foreach ($all_divs as $div)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="divs[]" id="div_{{ $div->pkey }}"
                                    value="{{ $div->pkey }}">
                                <label class="form-check-label" for="div_{{ $div->pkey }}" title="{{ $div->divdescr }}">
                                    {{ $div->name }}
                                </label>
                            </div>
                            @endforeach
                        </fieldset>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="form-group">
                        <label>First Event</label>
                        <fieldset name="firstevent">
                            @foreach ($all_events as $event)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="firstevent" id="event_{{ $event->pkey }}"
                                    value="{{ $event->pkey }}">
                                <label class="form-check-label" for="event_{{ $event->pkey }}">
                                    {{ $event->title }}
                                </label>
                            </div>
                            @endforeach
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="bio">About Me</label>
                        <textarea class="form-control" id="bio" name="bio" maxlength="255"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="rlimpact">Real Life</label>
                        <textarea class="form-control" id="rlimpact" name="rlimpact" maxlength="255"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="skills">Skills</label>
                <select class="custom-select" name="skills[]" multiple size="28">
                    @php
                        $in_group = false;
                    @endphp
                    @foreach ($all_skills as $skill)
                    @if (!$skill->parentid)
                        @if ($in_group)
                        </optgroup>
                        @endif
                        <optgroup label="{{ $skill->skillname }}">
                        @php
                            $in_group = true;
                        @endphp
                    @else
                    <option value="{{ $skill->pkey }}">
                        {{ $skill->skillname }}
                    </option>
                    @endif
                @endforeach
                </optgroup>
                </select>
                <small id="skillsHelpBlock" class="form-text text-muted">
                    Hold down the Ctrl (Windows, Linux) or Command (MacOS) button to select multiple options.
                </small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="submit" class="btn btn-primary float-right">Submit</button>
        </div>
    </div>
</form>
@endsection
