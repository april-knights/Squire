@extends('layouts.app')

@section('title', 'Editing ' . $knight->rname)

@section('content')
<form method="POST">
    @csrf
    <div class="row">
        <div class="col">
            <label for="rname"><h2>Reddit Name</h2></label>
            <input id="rname" type="text" value="{{ $knight->rname }}"></input>
        </div>
        <div class="col">
            <label for="dname"><h2>Discord Name</h2></label>
            <input id="dname" type="text" value="{{ $knight->dname }}"></input>
        </div>
        <div class="col">
            <label for="email"><h2>Email</h2></label>
            <input id="email" type="email" value="{{ $knight->email }}"></input>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <label for="batt"><h2>Battalion</h2></label>
                    @foreach ($all_batts as $batt)
                    <fieldset class="form-check">
                        <input class="form-check-input" type="radio" name="batt" id="{{ $batt->pkey }}"
                            value="{{ $batt->pkey }}" @if ($batt->pkey == $cur_batt) checked @endif>
                        <label class="form-check-label" for="{{ $batt->pkey }}">
                            {{ $batt->name }}
                        </label>
                    </fieldset>
                    @endforeach
                </div>
                <div class="col-md-6">
                    <label for="rank"><h2>Rank</h2></label>
                    @foreach ($all_ranks as $rank)
                    <fieldset class="form-check">
                        <input class="form-check-input" type="radio" name="rank" id="{{ $rank->pkey }}"
                            value="{{ $rank->pkey }}" @if ($rank->pkey == $cur_rank) checked @endif>
                        <label class="form-check-label" for="{{ $rank->pkey }}">
                            {{ $rank->name }}
                        </label>
                    </fieldset>
                    @endforeach
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="divs"><h2>Divisions</h2></label>
                    @foreach ($all_divs as $div)
                    <fieldset class="form-check">
                        <input class="form-check-input" type="checkbox" name="divs" id="{{ $batt->pkey }}"
                            value="" @if (in_array($div, $cur_divs)) checked @endif>
                        <label class="form-check-label" for="{{ $div->pkey }}">
                            {{ $div->name }}
                        </label>
                    </fieldset>
                    @endforeach
                </div>
                <div class="col-md-6">

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label for="skills"><h2>Skills</h2></label>
            <select class="custom-select" multiple size="28">
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
            <textarea id="bio">{{ $knight->bio }}</textarea>
        </div>
        <div class="col">
            <label for="rlimpact"><h2>Real Life</h2></label>
            <textarea id="rlimpact">{{ $knight->rlimpact }}</textarea>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="submit" class="btn btn-primary float-right">Submit</button>
        </div>
    </div>
</form>
@endsection
