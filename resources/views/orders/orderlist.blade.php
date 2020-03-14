@forelse ($orders as $order)
    @component('orders.order', ['order' => $order])
    @endcomponent
@empty
    <p>No orders yet</p>
@endforelse
