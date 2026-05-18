@extends('layouts.app')

@section('title', 'Results Locked')

@section('content')
<div style="text-align:center;padding:3rem 1rem;">
    <i class="fas fa-lock" style="font-size:3rem;color:#8b3a3a;margin-bottom:1rem;"></i>
    <h3>Results Require Passphrase</h3>
    <p style="color:#c0a0a0;max-width:480px;margin:0.75rem auto 0;">
        Ballot decryption requires your election passphrase. Please authenticate
        via the voting controls on the EA dashboard.
    </p>
    <a href="{{ route('election.dashboard') }}"
       style="margin-top:1.5rem;display:inline-block;background:#8b3a3a;border:1px solid #a04040;color:#efefef;padding:0.4rem 1.25rem;border-radius:4px;text-decoration:none;">
        <i class="fas fa-tachometer-alt mr-1"></i> Back to Dashboard
    </a>
</div>
@endsection