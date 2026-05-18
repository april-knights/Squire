@extends('layouts.app')

@section('title', 'Home')

@section('content')

@include('partials.election-home-widget')

<div style="margin-top: 1rem;">
    <h4>Welcome to Squire</h4>
    <p>
        Squire is the organisational hub for the April Knights. Use the navigation above to access your profile, battalions, divisions, orders, and links.
    </p>
    <p>
        If you discover something that needs correcting or would be useful to have, please drop a note to
        <a href="mailto:askthearcaenum@aprilknights.org?subject=Squire%20Feedback" style="color:#c0a0a0;">AskTheArcaenum@aprilknights.org</a>.
    </p>
</div>

@endsection