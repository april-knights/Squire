<div class="order">
    <h3 class="d-inline-block">{{ $order->title }}</h3>
    by
    <a href="/profile/{{ $order->rname }}">
        {{ $order->rname }}
    </a>
    {{-- clean() calls the HTML Purifier to prevent XSS injection --}}
    {!! clean($order->body) !!}
</div>
