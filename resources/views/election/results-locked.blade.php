@extends('layouts.app')

@section('title', 'Results')

@section('content')
<style>
.locked-card {
    background-color: #6b2b2b;
    border: 1px solid #8b3a3a;
    border-radius: 6px;
    padding: 1.75rem 1.5rem;
    color: #efefef;
}
.locked-card .ea-input {
    background-color: #3a1a1a;
    border: 1px solid #8b3a3a;
    color: #efefef;
    border-radius: 4px;
    padding: 0.45rem 0.75rem;
    font-size: 0.95rem;
    width: 100%;
    margin-bottom: 0.75rem;
}
.locked-card .ea-input:focus {
    outline: none;
    border-color: #efefef;
}
.locked-card .btn-ea {
    background-color: #8b3a3a;
    border: 1px solid #a04040;
    color: #efefef;
    padding: 0.45rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
    cursor: pointer;
    width: 100%;
}
.locked-card .btn-ea:hover { background-color: #a04040; }
</style>

<div style="max-width:520px;margin:2rem auto;">
    <div class="locked-card">
        <div style="text-align:center;margin-bottom:1rem;">
            <i class="fas fa-lock" style="font-size:2.5rem;color:#8b3a3a;margin-bottom:0.75rem;display:block;"></i>
            <h4 style="margin-bottom:0.5rem;">Results Require Your Passphrase</h4>
            <p style="color:#c0a0a0;font-size:0.88rem;margin:0;">
                Ballots are decrypted only while you enter your election passphrase. It is never stored,
                and the results page is not cached by your browser.
            </p>
        </div>

        <form method="POST" action="{{ route('election.results') }}">
            @csrf
            <input type="password" name="passphrase" class="ea-input" required autocomplete="off"
                placeholder="Election passphrase" autofocus>
            <button type="submit" class="btn-ea">
                <i class="fas fa-unlock mr-1"></i> Unlock Results
            </button>
        </form>

        <div style="text-align:center;margin-top:1rem;">
            <a href="{{ route('election.dashboard') }}" style="color:#c0a0a0;font-size:0.85rem;">← Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection