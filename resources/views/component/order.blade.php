@php /** @var \App\Model\Order $order */ @endphp
<div class="order">
    <h3 class="d-inline-block">{{ $order->title }}</h3>
    by
    <a href="/profile/{{ $order->author->rname }}">
        {{ $order->author->rname }}
    </a>
    {{-- clean() calls the HTML Purifier to prevent XSS injection --}}
    {!! clean($order->body) !!}
</div>
