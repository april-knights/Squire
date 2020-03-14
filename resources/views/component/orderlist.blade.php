@forelse ($orders as $order)
    @component('component.order', ['order' => $order])
    @endcomponent
@empty
    <p>No orders yet</p>
@endforelse
