<div class="row">
    <div class="col">
        <h2>Battalion Name</h2>
        <p>{{ $batt->name }}</p>
    </div>
    <div class="col">
        <h2>Battalion Leader</h2>
        @if ($battlead)
        <a href="/profile/{{ $battlead->rname }}">
            {{ $battlead->rname }}
        </a>
        @else
        <p>No one</p>
        @endif
    </div>
    <div class="col">
        <img class="banner" src="{{ asset('static/img/batt_banners/' . $batt->battalias . '.png') }}"
             alt="{{ $batt->name }} Banner" title="{{ $batt->name }} Banner">
    </div>
</div>
