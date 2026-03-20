@forelse ($orders as $order)
    <div style="position: relative;">
        @if($can_delete ?? false)
        <div style="position: absolute; left: -25px; top: 8px;">
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