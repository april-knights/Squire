@php /** @var \App\Model\Order $order */ @endphp
<div class="row order">
    <div class="col">
        <h3 class="d-inline-block">{{ $order->title }}</h3>
        by
        <a href="/profile/{{ $order->author->rname }}">
            {{ $order->author->rname }}
        </a>
        <p>{!! clean($order->body) !!}</p>
        {{-- clean() calls the HTML Purifier to prevent XSS injection --}}
            </div>
    <div class="col-auto">
        @if($can_edit_order ?? false)
        <a href="/orders/{{ $order->pkey }}/edit"><i class="fas fa-edit"></i></a>
        @endif
    </div>
</div>