@extends('layouts.app')

@section('title', 'Battalions')

@section('content')
<?php /** @var iterable<\App\Model\Battalion> $batts */ ?>
<table class="table table-hover table-borderless">
    <thead>
        <tr>
            <th scope="col">Battalion Name</th>
            <th scope="col">Battalion Leader</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($batts as $batt)
            <tr>
                <td><a href="/battalion/{{ $batt->battalias }}">{{ $batt->name }}</a></td>
                <td><a href="/profile/{{ $batt->leader?->rname }}">{{ $batt->leader?->rname }}</a></td>
            </tr>
        @empty
            <tr>
                <td>None</td>
                <td>No one</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
