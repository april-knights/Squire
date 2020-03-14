<table class="table table-hover table-borderless">
    <thead>
        <tr>
            <th scope="col">Reddit Name</th>
            <th scope="col">Discord Name</th>
            <th scope="col">Rank</th>
            <th scope="col">1st Event</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($members as $member)
        <tr>
            <td>
                <a href="/profile/{{ $member->rname }}">
                    {{ $member->rname }}
                </a>
            </td>
            <td>{{ $member->dname }}</td>
            <td>
            @if($member->name)
                {{ $member->name }}
                <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{ $member->rankdescr }}"></i>
            @endif
            </td>
            <td>{{ $member->title }}</td>
        </tr>
        @empty
        <tr>
            <td>None</td>
            <td>None</td>
            <td>None</td>
            <td>None</td>
        </tr>
        @endforelse
    </tbody>
</table>
