@extends('layouts.app')

@section('title', 'Election Schedule')

@push('styles')
<style>
.breadcrumb { background-color: rgba(0,0,0,0.25); border: 1px solid #8b3a3a; }
.breadcrumb-item a { color: #efefef; }
.breadcrumb-item.active { color: #c9a0a0; }
.breadcrumb-item + .breadcrumb-item::before { color: #8b3a3a; }
</style>
@endpush

@section('content')
<style>
.sched-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    color: #efefef;
}
.sched-card h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #c0a0a0;
    margin-bottom: 1rem;
}
.sched-row { margin-bottom: 0.9rem; }
.sched-label { font-size: 0.8rem; color: #c0a0a0; display: block; margin-bottom: 0.3rem; }
.ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    width: 100%;
    max-width: 320px;
}
.ea-input.wide { max-width: none; }
.ea-input:focus { outline: none; border-color: #efefef; }
.note { font-size: 0.78rem; color: #c0a0a0; margin-top: 0.25rem; }
.btn-ea {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.4rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
    margin-right: 0.5rem;
}
.btn-ea:hover { background-color: #a04040; }
.btn-ea.muted { background-color: #5a2424; border-color: #8b3a3a; }
.banner {
    background-color: #4a2020;
    border: 1px solid #f0ad4e;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    color: #f0ad4e;
    font-size: 0.88rem;
    margin-bottom: 1rem;
}
.pill {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
    background: #8b3a3a;
    vertical-align: middle;
    margin-left: 0.5rem;
}
</style>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Home</a></li>
        <li class="breadcrumb-item active">Election Schedule</li>
    </ol>
</nav>

<h2>Election {{ $election->election_year }} — Schedule <span class="pill">{{ ucfirst($election->phase) }}</span></h2>
<p class="text-muted">
    Editable by Admins, the Grandmaster and Archmages. Dates are informational: phases are still advanced by hand
    and nothing happens automatically at these times. Every change is recorded in the election's phase log.
</p>

@if($locked)
<div class="banner"><i class="fas fa-lock mr-1"></i> This election is complete. Its schedule is locked.</div>
@endif

<div class="sched-card">
    <h5><i class="fas fa-calendar-alt mr-1"></i> Statutory timeline</h5>

    <form method="POST" action="{{ route('election.schedule.update') }}">
        @csrf

        @foreach($schedule['items'] as $item)
        <div class="sched-row">
            <label class="sched-label" for="f_{{ $item['key'] }}">{{ $item['label'] }} (UTC)</label>
            <input type="datetime-local" id="f_{{ $item['key'] }}" name="{{ $item['key'] }}" class="ea-input"
                value="{{ old($item['key'], $item['at'] ? $item['at']->format('Y-m-d\TH:i') : '') }}"
                data-suggest="{{ $suggested[$item['key']] }}" {{ $locked ? 'disabled' : '' }}>
            <div class="note" data-local-for="{{ $item['key'] }}"></div>
        </div>
        @endforeach

        @if($schedule['extended_until'])
        <div class="note" style="margin-bottom:0.75rem;">
            <i class="fas fa-clock mr-1"></i>
            The Election Administrator extended voting to {{ $schedule['extended_until']->format('M j, Y H:i') }} UTC.
            The scheduled voting close can no longer be changed.
        </div>
        @endif

        <div class="note" style="margin-bottom:0.75rem;">
            Enter times in UTC. In winter, midnight Eastern is 05:00 UTC. Leave a date blank if it is not set yet.
            The "Fill suggested dates" button uses the spec's calculation (nominations Jan 7&ndash;15, voting Jan 22 &ndash; Feb 5,
            all at 05:00 UTC); it only fills the form, nothing is saved until you press Save.
        </div>

        @unless($locked)
        <button type="button" id="fill-suggested" class="btn-ea muted">Fill suggested dates</button>
        <button type="submit" class="btn-ea"><i class="fas fa-save mr-1"></i> Save schedule</button>
        @endunless
    </form>
</div>

<div class="sched-card">
    <h5><i class="fab fa-reddit-alien mr-1"></i> Registration thread</h5>

    <form method="POST" action="{{ route('election.schedule.registration-thread') }}">
        @csrf
        <div class="sched-row">
            <label class="sched-label" for="registration_thread_url">Reddit link to the registration thread</label>
            <input type="url" id="registration_thread_url" name="registration_thread_url" class="ea-input wide"
                value="{{ old('registration_thread_url', $election->registration_thread_url) }}"
                placeholder="https://www.reddit.com/r/AprilKnights/comments/..." {{ $locked ? 'disabled' : '' }}>
            <div class="note">
                Post the thread on Reddit yourself, with a link back to Squire for registering, then paste its link here.
                Knights will see a link to it next to the register button. Leave blank to clear it.
            </div>
        </div>
        @unless($locked)
        <button type="submit" class="btn-ea"><i class="fas fa-save mr-1"></i> Save link</button>
        @endunless
    </form>
</div>

<script>
(function () {
    function local(v) {
        if (!v) { return ''; }
        var d = new Date(v + ':00Z');
        if (isNaN(d.getTime())) { return ''; }
        return 'Your local time: ' + d.toLocaleString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: '2-digit', timeZoneName: 'short'
        });
    }

    function refresh(input) {
        var out = document.querySelector('[data-local-for="' + input.name + '"]');
        if (out) { out.textContent = local(input.value); }
    }

    document.querySelectorAll('input[type="datetime-local"]').forEach(function (input) {
        refresh(input);
        input.addEventListener('input', function () { refresh(input); });
    });

    var btn = document.getElementById('fill-suggested');
    if (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('input[data-suggest]').forEach(function (input) {
                input.value = input.getAttribute('data-suggest');
                refresh(input);
            });
        });
    }
})();
</script>
@endsection