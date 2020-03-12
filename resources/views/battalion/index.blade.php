@extends("layouts.app")

@section("title", "Battalions")

@section("content")
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
                <td>{{ $batt->name }}</td>
                <td>{{ $batt->rname }}</td>
            </tr>
            @empty
            <tr>
                <td>None</td>
                <td>Noone</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
