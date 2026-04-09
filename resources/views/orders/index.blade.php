@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<?php /** @var \App\Model\Knight $user */ ?>

<form method="POST" action="/orders" id="ordersForm">
    @csrf
    @method('DELETE')

    @if($battalion_orders->isNotEmpty())
    <h2>Battalion Orders</h2>
    @component('component.orderlist', [
        'orders'         => $battalion_orders,
        'read_ids'       => $battalion_read_ids,
        'can_edit_order' => $can_create && Auth::user()->isOfficer(Auth::user()->batt),
        'can_delete'     => $can_delete,
    ])
    @endcomponent
    @endif

    <h2>Knights</h2>
    @component('component.orderlist', [
        'orders'         => $knight_orders,
        'read_ids'       => $knight_read_ids,
        'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
        'can_delete'     => $can_delete,
    ])
    @endcomponent

    @if($user_rank <= \App\Model\Rank::HIGHEST_OFFICER_RANK)
    <h2>Officers</h2>
    @component('component.orderlist', [
        'orders'         => $officer_orders,
        'read_ids'       => $officer_read_ids,
        'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
        'can_delete'     => $can_delete,
    ])
    @endcomponent
    @endif

    @if($user_rank <= \App\Model\Rank::HIGHEST_COMMANDER_RANK)
    <h2>Commanders</h2>
    @component('component.orderlist', [
        'orders'         => $commander_orders,
        'read_ids'       => $commander_read_ids,
        'can_edit_order' => $can_create && $user_rank <= \App\Model\Rank::HIGHEST_COUNCILOR_RANK,
        'can_delete'     => $can_delete,
    ])
    @endcomponent
    @endif

    @if($can_create || $can_delete)
    <div class="row mt-3">
        @if($can_create)
        <div class="col">
            <a href="/orders/create" class="btn btn-success">New Order</a>
        </div>
        @endif
        @if($can_delete)
        <div class="col">
            <button type="submit" class="btn btn-danger float-right" form="ordersForm"
                data-toggle="confirmation"
                data-btn-ok-icon-class="fas fa-check"
                data-btn-cancel-icon-class="fas fa-ban">
                Delete Selected
            </button>
        </div>
        @endif
    </div>
    @endif
</form>

@if($can_create)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.querySelectorAll('.orderlist-container').forEach(function(container) {
    new Sortable(container, {
        animation: 150,
        handle: '.order-item',
        onEnd: function(evt) {
            var orders = [];
            container.querySelectorAll('.order-item').forEach(function(item) {
                orders.push(item.dataset.id);
            });
            fetch('/orders/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ orders: orders })
            });
        }
    });
});
</script>
@endif

<script>
// Order read tracking — fire once on first expand
$(document).on('show.bs.collapse', '.orderlist-container .collapse', function () {
    var bodyId  = $(this).attr('id');
    var orderId = bodyId.replace('order-body-', '');
    var $item   = $(this).closest('.order-item');

    if ($item.data('read') === 'true') return;

    $item.data('read', 'true');
    $item.removeClass('order-unread');
    $item.find('.fa-exclamation-circle').remove();
    $item.find('.order-title').removeClass('font-weight-bold');

    $.post('/orders/' + orderId + '/read', {
        _token: $('meta[name="csrf-token"]').attr('content')
    });
});

// Rotate chevron on expand/collapse
$(document).on('show.bs.collapse hide.bs.collapse', '.orderlist-container .collapse', function (e) {
    var $icon = $(this).closest('.order-item').find('.order-toggle-icon');
    $icon.html(e.type === 'show' ? '&#9650;' : '&#9660;');
});
</script>

@endsection