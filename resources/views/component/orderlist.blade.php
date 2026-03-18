@forelse ($orders as $order)
    @component('component.order', [
        'order'          => $order,
        'can_edit_order' => $can_edit_order ?? false,
        'can_delete'     => $can_delete ?? false,
    ])
    @endcomponent
@empty
    <p>No orders at this time.</p>
@endforelse
