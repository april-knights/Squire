<?php /** @var iterable<\App\Model\Knight> $members */ ?>
@php
    $next_direction = $direction === 'asc' ? 'desc' : 'asc';
    $sort_icon = $direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
@endphp
<table class="table table-hover table-borderless">
    <thead>
        <tr>
            <th scope="col">
                <a href="/battalion/{{ $alias }}/members?sort=rname&direction={{ $sort === 'rname' ? $next_direction : 'asc' }}">
                    Reddit Name
                    @if($sort === 'rname') <i class="fas {{ $sort_icon }}"></i> @endif
                </a>
            </th>
            <th scope="col">
                <a href="/battalion/{{ $alias }}/members?sort=dname&direction={{ $sort === 'dname' ? $next_direction : 'asc' }}">
                    Discord Name
                    @if($sort === 'dname') <i class="fas {{ $sort_icon }}"></i> @endif
                </a>
            </th>
            <th scope="col">
                <a href="/battalion/{{ $alias }}/members?sort=rnk&direction={{ $sort === 'rnk' ? $next_direction : 'asc' }}">
                    Rank
                    @if($sort === 'rnk') <i class="fas {{ $sort_icon }}"></i> @endif
                </a>
            </th>
            <th scope="col">
                <a href="/battalion/{{ $alias }}/members?sort=firstevent&direction={{ $sort === 'firstevent' ? $next_direction : 'asc' }}">
                    1st Event
                    @if($sort === 'firstevent') <i class="fas {{ $sort_icon }}"></i> @endif
                </a>
            </th>
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
            @if($member->rank->name)
                {{ $member->rank->name }}
                <i class="explainer fas fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{ $member->rank->rankdescr }}"></i>
            @endif
            </td>
            <td>{{ $member->firstEvent?->title }}</td>
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
<script>
document.getElementById('memberSearch').addEventListener('input', function() {
    var search = this.value.toLowerCase();
    var rows = document.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>