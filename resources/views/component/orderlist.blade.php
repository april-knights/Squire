<div class="orderlist-container">
@forelse ($orders as $order)
    @php $isRead = isset($read_ids[$order->pkey]); @endphp
    <div class="order-item {{ $isRead ? '' : 'order-unread' }}"
         data-id="{{ $order->pkey }}"
         data-read="{{ $isRead ? 'true' : 'false' }}"
         style="position: relative;">

        @if($can_delete ?? false)
        <div style="position: absolute; left: -25px; top: 8px;">
            <input type="checkbox" name="orders[]" value="{{ $order->pkey }}">
        </div>
        @endif

        {{-- Collapse toggle header --}}
        <div class="order-toggle"
             data-toggle="collapse"
             data-target="#order-body-{{ $order->pkey }}"
             aria-expanded="false"
             aria-controls="order-body-{{ $order->pkey }}">
            @if(!$isRead)
                <i class="fas fa-exclamation-circle text-danger" title="Unread"></i>
            @endif
            <span class="order-title {{ $isRead ? '' : 'font-weight-bold' }}">
                {{ $order->title }}
            </span>
            <span class="order-toggle-icon text-muted small ml-2">&#9660;</span>
        </div>

        {{-- Collapsible body --}}
        <div class="collapse" id="order-body-{{ $order->pkey }}">
            @component('component.order', [
                'order'          => $order,
                'can_edit_order' => $can_edit_order ?? false,
                'can_delete'     => $can_delete ?? false,
            ])
            @endcomponent
        </div>

    </div>
@empty
    <p>No orders at this time.</p>
@endforelse
</div>