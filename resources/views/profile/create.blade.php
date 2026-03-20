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
<form method="POST" id="create">
    @csrf
    <div class="row">
        <div class="col-sm">
            <div class="form-group">
                <label for="knum">Knight Number</label>
                <input class="form-control" id="knum" name="knum" type="text" size="6"
                    value="{{ $next_knum }}" pattern="\d{6}" inputmode="numeric" readonly required>
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
                <input class="form-control" id="dname" name="dname" type="text"></input>
                <small id="dnameHelpBlock" class="form-text text-muted">
                    Format: username
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
                        @if ($is_commander)
                        <input class="form-control" type="text" value="{{ Auth::user()->battalion->name }}" readonly>
                        <input type="hidden" name="batt" value="{{ $user_batt }}">
                        @else
                        <select class="custom-select" name="batt">
                            @foreach ($all_batts as $batt)
                            <option value="{{ $batt->pkey }}" label="{{ $batt->name }}"
                                @if ($batt->pkey == $def_batt) selected @endif>
                                {{ $batt->name }}
                            </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label>Rank</label>
                        @if ($is_commander)
                        <input class="form-control" type="text" value="Initiate" readonly>
                        @else
                        <select class="custom-select" name="rank">
                            @foreach ($all_ranks as $rank)
                            <option value="{{ $rank->pkey }}" label="{{ $rank->name }}"
                                @if ($rank->pkey == $def_rank) selected @endif>
                                {{ $rank->name }}
                            </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label>Security</label>
                        @if ($is_commander)
                        <input class="form-control" type="text" value="Initiate" readonly>
                        @else
                        <select class="custom-select" name="security">
                            @foreach ($all_secs as $sec)
                            <option value="{{ $sec->pkey }}" label="{{ $sec->secname }}"
                                @if ($sec->pkey == $def_sec) selected @endif>
                                {{ $sec->secname }}
                            </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <div class="form-group">
                        <label>Divisions</label>
                        <fieldset name="divs">
                        @foreach ($all_divs as $div)
                        @if ($div->pkey != 5 || Auth::user()->canManageInquisition())
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="divs[]" id="div_{{ $div->pkey }}"
                                value="{{ $div->pkey }}">
                            <label class="form-check-label" for="div_{{ $div->pkey }}" title="{{ $div->divdescr }}">
                                {{ $div->name }}
                            </label>
                        </div>
                        @endif
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
                    @if (!$is_commander)
                    <div class="form-group">
                        <label for="onote">Officer Note</label>
                        <textarea class="form-control" id="onote" name="onote" maxlength="1000"></textarea>
                    </div>
                    @endif
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

<div class="modal fade" id="emailWarningModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
<div class="modal-content" style="background-color: #5a2424; color: #efefef; border: 1px solid #8b3a3a;">
    <div class="modal-header" style="border-bottom: 1px solid #8b3a3a;">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> No Email Address</h5>
        <button type="button" class="close" data-dismiss="modal" style="color: #efefef;">
            <span>&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <p>This knight has no email address. Knights without an email may miss important communications.</p>
        <p>Are you sure you want to continue without an email address?</p>
    </div>
    <div class="modal-footer" style="border-top: 1px solid #8b3a3a;">
        <button type="button" class="btn btn-secondary" id="addEmailBtn" data-dismiss="modal">Add Email</button>
        <button type="button" class="btn btn-warning" id="continueWithoutEmailBtn">Continue Without Email</button>
    </div>
</div>
    </div>
</div>

<script>
    document.getElementById('create').addEventListener('submit', function(e) {
        var email = document.getElementById('email').value.trim();
        if (email === '') {
            e.preventDefault();
            $('#emailWarningModal').modal('show');
        }
    });

    document.getElementById('addEmailBtn').addEventListener('click', function() {
        document.getElementById('email').focus();
    });

    document.getElementById('continueWithoutEmailBtn').addEventListener('click', function() {
        $('#emailWarningModal').modal('hide');
        document.getElementById('create').submit();
    });
</script>
@endsection