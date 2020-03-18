@extends('layouts.app')

@section('title', 'Links')

@section('content')

<h2>Subreddits</h2>
@component('component.links', ['links' => $subreddit_links])
@endcomponent

<h2>Events</h2>
@component('component.links', ['links' => $event_links])
@endcomponent

<h2>Discord Servers</h2>
@component('component.links', ['links' => $discord_links])
@endcomponent

<h2>Documents</h2>
@component('component.links', ['links' => $document_links])
@endcomponent

@endsection
