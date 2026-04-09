@php /** @var \App\Model\Order $order */ @endphp
<div class="row order pt-2">
    <div class="col">
        <small class="text-muted">
            by <a href="/profile/{{ $order->author->rname }}">{{ $order->author->rname }}</a>
            &mdash; {{ $order->crtsetdt->format('d M Y') }}
        </small>
        <div class="mt-2">{!! clean($order->body) !!}</div>
        {{-- clean() calls the HTML Purifier to prevent XSX injection --}}
    </div>
    <div class="col-auto">
        @if($can_edit_order ?? false)
        <a href="/orders/{{ $order->pkey }}/edit"><i class="fas fa-edit"></i></a>
        @endif
    </div>
</div>