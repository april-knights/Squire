@extends('layouts.app')

@section('title', 'Already Voted')

@section('content')
<div style="text-align:center;padding:3rem 1rem;">
    <i class="fas fa-check-circle" style="font-size:3rem;color:#5cb85c;margin-bottom:1rem;"></i>
    <h3>Ballot Already Submitted</h3>
    <p style="color:#c0a0a0;max-width:480px;margin:0.75rem auto 0;">
        You have already cast your ballot for the {{ $election->election_year }} election.
        Submissions are final and cannot be changed.
    </p>
    <a href="/" class="btn-election" style="margin-top:1.5rem;display:inline-block;background:#8b3a3a;border:1px solid #a04040;color:#efefef;padding:0.4rem 1.25rem;border-radius:4px;text-decoration:none;">
        Return Home
    </a>
</div>
@endsection