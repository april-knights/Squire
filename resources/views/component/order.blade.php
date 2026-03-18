@php /** @var \App\Model\Order $order */ @endphp
<div class="row order">
    <div class="col">
        <h3 class="d-inline-block">{{ $order->title }}</h3>
        by
        <a href="/profile/{{ $order->author->rname }}">
            {{ $order->author->rname }}
        </a>
        {{-- clean() calls the HTML Purifier to prevent XSS injection --}}
        {!! clean($order->body) !!}
    </div>
    <div class="col-auto">
        @if($can_edit_order ?? false)
        <a href="/orders/{{ $order->pkey }}/edit"><i class="fas fa-edit"></i></a>
        @endif
    </div>
    <div class="col-auto">
        @if($can_delete ?? false)
        <form method="POST" action="/orders/{{ $order->pkey }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-link p-0"
                data-toggle="confirmation"
                data-btn-ok-icon-class="fas fa-check"
                data-btn-cancel-icon-class="fas fa-ban">
                <i class="fas fa-trash"></i>
            </button>
        </form>
        @endif
    </div>
</div>
