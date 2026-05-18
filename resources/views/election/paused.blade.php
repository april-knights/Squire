@extends('layouts.app')

@section('title', 'Voting Paused')

@section('content')
<div style="text-align:center;padding:3rem 1rem;">
    <i class="fas fa-pause-circle" style="font-size:3rem;color:#8b3a3a;margin-bottom:1rem;"></i>
    <h3>Voting is Temporarily Paused</h3>
    <p style="color:#c0a0a0;max-width:480px;margin:0.75rem auto 0;">
        The Election Administrator has temporarily paused voting. This is usually brief.
        Please check back shortly — your ballot will be waiting for you.
    </p>
    <a href="/" class="btn-election" style="margin-top:1.5rem;display:inline-block;background:#8b3a3a;border:1px solid #a04040;color:#efefef;padding:0.4rem 1.25rem;border-radius:4px;text-decoration:none;">
        Return Home
    </a>
</div>
@endsection