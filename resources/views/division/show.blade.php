@extends("layouts.app")

@section("title", $div->name)

@section("content")
    <div class="row">
        <div class="col">
            <h2>Division Name</h2>
            <p>{{ $div->name }}</p>
        </div>
        <div class="col">
            <h2>Division Leader</h2>
            @if ($divlead)
            <a href="/profile/{{ $divlead->rname }}">
                {{ $divlead->rname }}
            </a>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <h2>Division Motto</h2>
                    <p>{{ $div->motto }}
                </div>
                <div class="col-md-6">
                    <h2>Division Officers</h2>
                    <ul class="officers">
                    @forelse ($officers as $officer)
                        <li>
                            <a href="/profile/{{ $officer->rname }}">
                                {{ $officer->rname }}
                            </a>
                        </li>
                    @empty
                        <li>Noone</li>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h2>Division Members</h2>
            <ul class="members">
            @forelse ($members as $member)
                <li>
                    <a href="/profile/{{ $member->rname }}">
                        {{ $member->rname }}
                    </a>
                </li>
            @empty
                <li>Noone</li>
            @endforelse
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h2>Division Description</h2>
            <p>{{ $div->divdescr }}</p>
        </div>
    </div>
@endsection
