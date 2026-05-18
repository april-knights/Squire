@extends('layouts.app')

@section('title', 'Cast Your Vote')

@section('content')
<style>
.ballot-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    color: #efefef;
}
.ballot-card h4 {
    margin-bottom: 0.25rem;
}
.ballot-card p {
    color: #c0a0a0;
    font-size: 0.9rem;
    margin-bottom: 0;
}
.candidate-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #5a2424;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.85rem 1rem;
    margin-bottom: 0.6rem;
    gap: 1rem;
}
.candidate-name {
    font-size: 1rem;
    font-weight: 500;
    flex: 1;
}
.candidate-rname {
    font-size: 0.8rem;
    color: #c0a0a0;
    margin-top: 0.1rem;
}
.rank-select {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.3rem 0.6rem;
    font-size: 0.9rem;
    width: 70px;
    text-align: center;
}
.rank-select:focus {
    outline: none;
    border-color: #efefef;
}
.ballot-warning {
    background-color: #4a1f1f;
    border: 1px solid #8b3a3a;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    color: #f0ad4e;
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: none;
}
.btn-submit-ballot {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.5rem 1.5rem;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.15s ease;
    width: 100%;
    margin-top: 0.5rem;
}
.btn-submit-ballot:hover {
    background-color: #a04040;
}
.btn-submit-ballot:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<div style="max-width:620px;margin:0 auto;">

    <div class="ballot-card">
        <h4><i class="fas fa-vote-yea mr-2"></i>{{ $election->election_year }} Grandmaster Election</h4>
        <p>Rank each candidate in order of your preference. Assign 1 to your first choice, 2 to your second, and so on. Each rank must be unique. Your submission is final.</p>
    </div>

    <div class="ballot-warning" id="ballot-warning">
        Please assign a unique rank to every candidate before submitting.
    </div>

    <form method="POST" action="{{ route('election.ballot.submit') }}" id="ballot-form">
        @csrf

        @foreach($candidates as $candidate)
        <div class="candidate-row">
            <div>
                <div class="candidate-name">{{ $candidate->knight->rname }}</div>
                <div class="candidate-rname">/u/{{ $candidate->knight->rname }}</div>
            </div>
            <select
                name="rankings[{{ $candidate->pkey }}]"
                class="rank-select"
                required
            >
                <option value="">—</option>
                @for($i = 1; $i <= count($candidates); $i++)
                <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
        @endforeach

        <div style="margin-top:1.25rem;">
            <button type="submit" class="btn-submit-ballot" id="submit-btn" disabled>
                <i class="fas fa-lock mr-1"></i> Submit Ballot
            </button>
            <p style="color:#c0a0a0;font-size:0.78rem;text-align:center;margin-top:0.5rem;">
                Your ballot is encrypted and cannot be changed after submission.
            </p>
        </div>
    </form>
</div>

<script>
(function () {
    var selects = document.querySelectorAll('.rank-select');
    var warning = document.getElementById('ballot-warning');
    var btn     = document.getElementById('submit-btn');
    var total   = selects.length;

    function validate() {
        var values = Array.from(selects).map(function (s) { return s.value; });
        var filled = values.filter(function (v) { return v !== ''; });

        // All filled
        if (filled.length < total) {
            warning.style.display = 'none';
            btn.disabled = true;
            return;
        }

        // All unique
        var unique = new Set(values);
        if (unique.size !== total) {
            warning.style.display = 'block';
            btn.disabled = true;
            return;
        }

        warning.style.display = 'none';
        btn.disabled = false;
    }

    selects.forEach(function (s) {
        s.addEventListener('change', validate);
    });

    // Prevent double submission
    document.getElementById('ballot-form').addEventListener('submit', function () {
        btn.disabled = true;
        btn.textContent = 'Submitting…';
    });
})();
</script>
@endsection