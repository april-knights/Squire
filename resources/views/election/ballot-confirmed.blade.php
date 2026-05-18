@extends('layouts.app')

@section('title', 'Vote Confirmed')

@section('content')
<div style="text-align:center;padding:3rem 1rem;">
    <i class="fas fa-vote-yea" style="font-size:3rem;color:#5cb85c;margin-bottom:1rem;"></i>
    <h3>Your Vote Has Been Recorded</h3>
    <p style="color:#c0a0a0;max-width:480px;margin:0.75rem auto 0;">
        Thank you for participating in the {{ $election->election_year }} Grandmaster election.
        Your encrypted ballot has been recorded. The Election Administrator will
        announce results after voting closes.
    </p>
    <p style="color:#c0a0a0;max-width:480px;margin:0.5rem auto 0;font-size:0.85rem;">
        An audit confirmation has been sent to the Election Administrator.
    </p>
    <a href="/" class="btn-election" style="margin-top:1.5rem;display:inline-block;background:#8b3a3a;border:1px solid #a04040;color:#efefef;padding:0.4rem 1.25rem;border-radius:4px;text-decoration:none;">
        Return Home
    </a>
</div>
@endsection