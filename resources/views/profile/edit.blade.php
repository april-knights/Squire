@extends('layouts.app')

<?php /** @var \App\Model\Knight $knight */ ?>
@section('title', 'Edit ' . $knight->rname)

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
<h1>Edit {{ $knight->rname }}</h1>
<form method="POST" id="edit">
    @csrf
    <div class="row">
        @if (in_array('rname', $editable_fields))
        <div class="col-md">
            <div class="form-group">
                <label for="rname">Reddit Name</label>
                <input class="form-control" id="rname" name="rname" type="text"
                    value="{{ $knight->rname }}">
                <small id="rnameHelpBlock" class="form-text text-muted">
                    Without the /u/
                </small>
            </div>
        </div>
        @endif
        <div class="col-md">
            <div class="form-group">
                <label for="dname">Discord Name</label>
                <input class="form-control" id="dname" name="dname" type="text" pattern=".*#\d{4}"
                     value="{{ $knight->dname }}" @check_disabled('dname')></input>
                <small id="dnameHelpBlock" class="form-text text-muted">
                    Format: Username#1234
                </small>
            </div>
        </div>
        {{-- If you can't edit emails you probably shouldn't see them either --}}
        @if (in_array('email', $editable_fields))
        <div class="col-md">
            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" id="email" name="email" type="email"
                    value="{{ $knight->email }}"></input>
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                @if (in_array('rname', $editable_fields))
                <div class="col-sm">
                    <div class="form-group">
                        <label>Battalion</label>
                        <select class="form-control" name="batt">
                            @php /** @var \App\Model\Battalion $batt */ @endphp
                            @foreach ($all_batts as $batt)
                            <option value="{{ $batt->pkey }}" label="{{ $batt->name }}"
                                @if ($batt->pkey == $knight->batt) selected @endif>
                                {{ $batt->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                @if (in_array('rank', $editable_fields))
                <div class="col-sm">
                    <div class="form-group">
                        <label>Rank</label>
                        <select class="form-control" name="rank">
                            @php /** @var \App\Model\Rank $rank */ @endphp
                            @foreach ($all_ranks as $rank)
                            <option value="{{ $rank->pkey }}" label="{{ $rank->name }}"
                                @if ($rank->pkey == $knight->rnk) selected @endif>
                                {{ $rank->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                @if (in_array('security', $editable_fields))
                <div class="col-sm">
                    <div class="form-group">
                        <label>Security</label>
                        <select class="form-control" name="security">
                            @php /** @var \App\Model\Security $sec */ @endphp
                            @foreach ($all_secs as $sec)
                            <option value="{{ $sec->pkey }}" label="{{ $sec->secname }}"
                                @if ($sec->pkey == $knight->security) selected @endif>
                                {{ $sec->secname }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                @if (in_array('divs', $editable_fields))
                <div class="col-sm">
                    <div class="form-group">
                        <label>Divisions</label>
                        <fieldset name="divs">
                            @php /** @var \App\Model\Division $div */ @endphp
                            @php /** @var \Illuminate\Support\Collection $cur_divs */ @endphp
                            @foreach ($all_divs as $div)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="divs[]" id="div_{{ $div->pkey }}"
                                    value="{{ $div->pkey }}" @if ($cur_divs->contains($div)) checked @endif>
                                <label class="form-check-label" for="div_{{ $div->pkey }}" title="{{ $div->divdescr }}">
                                    {{ $div->name }}
                                </label>
                            </div>
                            @endforeach
                        </fieldset>
                    </div>
                </div>
                @endif
                @if (in_array('firstevent', $editable_fields))
                <div class="col-sm">
                    <div class="form-group">
                        <label>First Event</label>
                        <fieldset name="firstevent">
                            @foreach ($all_events as $event)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="firstevent" id="event_{{ $event->pkey }}"
                                    value="{{ $event->pkey }}" @if ($event->pkey == $knight->firstevent) checked @endif>
                                <label class="form-check-label" for="event_{{ $event->pkey }}">
                                    {{ $event->title }}
                                </label>
                            </div>
                            @endforeach
                        </fieldset>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="bio">About Me</label>
                        <textarea class="form-control" id="bio" name="bio" maxlength="255">{{ $knight->bio }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="rlimpact">Real Life</label>
                        <textarea class="form-control" id="rlimpact" name="rlimpact" maxlength="255">{{ $knight->rlimpact }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="skills">Skills</label>
                <select class="form-control" name="skills[]" multiple size="28">
                    @php
                        $in_group = false;
                    @endphp
                    @php /** @var \App\Model\Skill $skill */ @endphp
                    @php /** @var \Illuminate\Support\Collection $cur_skills */ @endphp
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
                    <option value="{{ $skill->pkey }}" @if ($cur_skills->contains($skill)) selected @endif>
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
        @if($can_delete)
        <div class="col">
            <div class="form-group">
                <button type="submit" class="btn btn-danger float-left" form="delete" data-toggle="confirmation"
                    data-btn-ok-icon-class="fas fa-check" data-btn-cancel-icon-class="fas fa-ban">Delete user</button>
            </div>
        </div>
        @endif
        <div class="col">
            <button type="submit" class="btn btn-success float-right">Submit</button>
        </div>
    </div>
</form>
@if($can_delete)
<form method="POST" id="delete" action="{{ route('profile', ['rname' => $knight->rname]) }}">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection
