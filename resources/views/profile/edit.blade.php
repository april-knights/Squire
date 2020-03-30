@extends('layouts.app')

@section('title', 'Editing ' . $knight->rname)

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
<form method="POST">
    @csrf
    <div class="row">
        <div class="col">
            <label for="rname"><h2>Reddit Name</h2></label>
            <input id="rname" name="rname" type="text" value="{{ $knight->rname }}" @check_disabled('rname')></input>
        </div>
        <div class="col">
            <label for="dname"><h2>Discord Name</h2></label>
            <input id="dname" name="dname" type="text" value="{{ $knight->dname }}" @check_disabled('dname')></input>
        </div>
        <div class="col">
            {{-- If you can't edit emails you probably shouldn't see them either --}}
            @if (in_array('email', $editable_fields))
            <label for="email"><h2>Email</h2></label>
            <input id="email" name="email" type="email" value="{{ $knight->email }}"></input>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    @if (in_array('batt', $editable_fields))
                    <h2>Battalion</h2>
                    <fieldset name="batt">
                        @foreach ($all_batts as $batt)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="batt" id="batt_{{ $batt->pkey }}"
                                value="{{ $batt->pkey }}" @if ($batt->pkey == $knight->batt) checked @endif>
                            <label class="form-check-label" for="batt_{{ $batt->pkey }}">
                                {{ $batt->name }}
                            </label>
                        </div>
                        @endforeach
                    </fieldset>
                    @endif
                </div>
                <div class="col-md-6">
                    @if (in_array('batt', $editable_fields))
                    <h2>Rank</h2>
                    <fieldset name="rank" class="form-check">
                        @foreach ($all_ranks as $rank)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rank" id="rank_{{ $rank->pkey }}"
                                value="{{ $rank->pkey }}" @if ($rank->pkey == $knight->rnk) checked @endif>
                            <label class="form-check-label" for="rank_{{ $rank->pkey }}">
                                {{ $rank->name }}
                            </label>
                        </div>
                        @endforeach
                    </fieldset>
                    @endif
                </div>
            </div>
            <div class="row">
                @if (in_array('divs', $editable_fields))
                <div class="col-md-6">
                    <label><h2>Divisions</h2></label>
                    <fieldset name="divs" class="form-check">
                        @foreach ($all_divs as $div)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="divs[]" id="div_{{ $div->pkey }}"
                                value="{{ $div->pkey }}" @if (in_array($div, $cur_divs)) checked @endif>
                            <label class="form-check-label" for="div_{{ $div->pkey }}">
                                {{ $div->name }}
                            </label>
                        </div>
                        @endforeach
                    </fieldset>
                </div>
                @endif
                <div class="col-md-6">
                    @if (in_array('firstevent', $editable_fields))
                    <h2>First Event</h2>
                    @foreach ($all_events as $event)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="firstevent" id="event_{{ $event->pkey }}"
                            value="{{ $event->pkey }}" @if ($event->pkey == $knight->firstevent) checked @endif>
                        <label class="form-check-label" for="event_{{ $event->pkey }}">
                            {{ $event->title }}
                        </label>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label for="skills"><h2>Skills</h2></label>
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
                <option value="{{ $skill->pkey }}" @if (in_array($skill, $cur_skills)) selected @endif>
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
    <div class="row">
        <div class="col">
            <label for="bio"><h2>About Me</h2></label>
            <textarea id="bio" name="bio" maxlength="255">{{ $knight->bio }}</textarea>
        </div>
        <div class="col">
            <label for="rlimpact"><h2>Real Life</h2></label>
            <textarea id="rlimpact" name="rlimpact" maxlength="255">{{ $knight->rlimpact }}</textarea>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="submit" class="btn btn-primary float-right">Submit</button>
        </div>
    </div>
</form>
@endsection
