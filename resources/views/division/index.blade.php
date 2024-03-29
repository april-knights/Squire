@extends('layouts.app')

@section('title', 'Divisions')

@section('content')
    <?php /** @var iterable<\App\Model\Division> $divs */ ?>
    <table class="table table-hover table-borderless">
        <thead>
        <tr>
            <th scope="col">Division Name</th>
            <th scope="col">Division Leader</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($divs as $div)
            <tr>
                <td><a href="/division/{{ $div->divalias }}">{{ $div->name }}</a></td>
                <td><a href="/profile/{{ $div->leader->rname }}">{{ $div->leader->rname }}</a></td>
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
