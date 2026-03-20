@forelse ($orders as $order)
    <div class="d-flex align-items-start">
        @if($can_delete ?? false)
        <div class="mr-2 mt-1">
            <input type="checkbox" name="orders[]" value="{{ $order->pkey }}">
        </div>
        @endif
        @component('component.order', [
            'order'          => $order,
            'can_edit_order' => $can_edit_order ?? false,
            'can_delete'     => $can_delete ?? false,
        ])
        @endcomponent
    </div>
@empty
    <p>No orders at this time.</p>
@endforelse