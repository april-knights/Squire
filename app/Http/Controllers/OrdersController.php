<?php

namespace App\Http\Controllers;

use App\Model\Battalion;
use App\Model\Order;
use App\Model\Rank;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\NotificationService;

use DB;
use Log;
use Auth;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $user   = Auth::user();
        $userId = Auth::id();

        $knight_orders = Order::where('level', '>', Rank::HIGHEST_OFFICER_RANK)
            ->with(['reads' => fn($q) => $q->where('fkeyknight', $userId)])
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('crtsetdt', 'desc')
            ->get();

        $officer_orders = collect();
        if ($user->getRankVal() <= Rank::HIGHEST_OFFICER_RANK) {
            $officer_orders = Order::where('level', '>', Rank::HIGHEST_COMMANDER_RANK)
                ->where('level', '<=', Rank::HIGHEST_OFFICER_RANK)
                ->with(['reads' => fn($q) => $q->where('fkeyknight', $userId)])
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderBy('crtsetdt', 'desc')
                ->get();
        }

        $commander_orders = collect();
        if ($user->getRankVal() <= Rank::HIGHEST_COMMANDER_RANK) {
            $commander_orders = Order::where('level', '<=', Rank::HIGHEST_COMMANDER_RANK)
                ->where('level', '>', 0)
                ->with(['reads' => fn($q) => $q->where('fkeyknight', $userId)])
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderBy('crtsetdt', 'desc')
                ->get();
        }

        $battalion_orders = Order::where('fkeybattalion', $user->batt)
            ->with(['reads' => fn($q) => $q->where('fkeyknight', $userId)])
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('crtsetdt', 'desc')
            ->get();

        $readIds = fn($collection) => $collection
            ->filter(fn($o) => $o->reads->isNotEmpty())
            ->pluck('pkey')
            ->flip()
            ->all();

        return view('orders.index', [
            'knight_orders'      => $knight_orders,
            'officer_orders'     => $officer_orders,
            'commander_orders'   => $commander_orders,
            'battalion_orders'   => $battalion_orders,
            'knight_read_ids'    => $readIds($knight_orders),
            'officer_read_ids'   => $readIds($officer_orders),
            'commander_read_ids' => $readIds($commander_orders),
            'battalion_read_ids' => $readIds($battalion_orders),
            'can_create'         => $user->checkSecurity('cmorder'),
            'can_delete'         => $user->checkSecurity('cdorder'),
            'user_rank'          => $user->getRankVal(),
        ]);
    }

    /**
     * Determine if the current user can edit the given order.
     *
     * @param Order $order
     * @return bool
     */
    private function canEdit(Order $order): bool
    {
        $user = Auth::user();

        if (!$user->checkSecurity('cmorder')) {
            return false;
        }

        // Councillor and higher can edit any order
        if ($user->getRankVal() <= Rank::HIGHEST_COUNCILOR_RANK) {
            return true;
        }

        // Commander/Lt can only edit battalion-scoped orders for their own battalion
        if ($order->fkeybattalion && $user->isOfficer($order->fkeybattalion)) {
            return true;
        }

        return false;
    }

    /**
     * Determine the level options available to the current user when creating.
     *
     * @return array
     */
    private function availableLevels(): array
    {
        $user = Auth::user();

        if ($user->getRankVal() <= Rank::HIGHEST_COUNCILOR_RANK) {
            return [
                0  => 'Battalion',
                3  => 'Commanders',
                6  => 'Officers',
                10 => 'Knights',
            ];
        }

        // Commander/Lt can only create battalion orders
        return [
            0 => 'Battalion',
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        abort_if(
            !Auth::user()->checkSecurity('cmorder'),
            401,
            'Not authorized to create orders.'
        );

        return view('orders.create', [
            'levels'   => $this->availableLevels(),
            'battalions' => Battalion::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        abort_if(
            !Auth::user()->checkSecurity('cmorder'),
            401,
            'Not authorized to create orders.'
        );

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string',
            'level'         => 'required|integer|in:' . implode(',', array_keys($this->availableLevels())),
            'fkeybattalion' => 'nullable|integer|exists:battalion,pkey',
        ]);

        // If level is not battalion, clear fkeybattalion
        if ($validated['level'] != 0) {
            $validated['fkeybattalion'] = null;
        }

        // Verify battalion order is scoped to user's own battalion unless councillor+
        if ($validated['level'] == 0) {
            $user = Auth::user();
            if ($user->getRankVal() > Rank::HIGHEST_COUNCILOR_RANK) {
                $validated['fkeybattalion'] = $user->batt;
            }
            abort_if(!$validated['fkeybattalion'], 422, 'Battalion orders must be assigned to a battalion.');
        }

        DB::transaction(function () use (&$validated, &$order) {
            $editor = Auth::id();
            $order  = new Order([
                'title'         => $validated['title'],
                'body'          => clean($validated['body']),
                'level'         => $validated['level'],
                'fkeybattalion' => $validated['fkeybattalion'] ?? null,
                'authorid'      => $editor,
                'crtsetid'      => $editor,
                'lstmdby'       => $editor,
            ]);
            $order->save();
        });

        // Fan-out outside the transaction — notification failure should not
        // roll back the order itself
        NotificationService::notifyNewOrder($order, Auth::id());

        return redirect('/orders');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);

        abort_if(!$this->canEdit($order), 401, 'Not authorized to edit this order.');

        return view('orders.edit', [
            'order'      => $order,
            'levels'     => $this->availableLevels(),
            'battalions' => Battalion::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (!$this->canEdit($order)) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to edit order ' . $id . '!');
            abort(401, 'Not authorized to edit this order.');
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'body'          => 'required|string',
            'level'         => 'required|integer|in:' . implode(',', array_keys($this->availableLevels())),
            'fkeybattalion' => 'nullable|integer|exists:battalion,pkey',
        ]);

        if ($validated['level'] != 0) {
            $validated['fkeybattalion'] = null;
        }

        if ($validated['level'] == 0) {
            $user = Auth::user();
            if ($user->getRankVal() > Rank::HIGHEST_COUNCILOR_RANK) {
                $validated['fkeybattalion'] = $user->batt;
            }
            abort_if(!$validated['fkeybattalion'], 422, 'Battalion orders must be assigned to a battalion.');
        }

        DB::transaction(function () use (&$validated, &$order) {
            $order->fill([
                'title'         => $validated['title'],
                'body'          => clean($validated['body']),
                'level'         => $validated['level'],
                'fkeybattalion' => $validated['fkeybattalion'] ?? null,
                'lstmdby'       => Auth::id(),
            ])->save();
        });

        return redirect('/orders');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $id)
    {
            if (!Auth::user()->checkSecurity('cdorder')) {
                Log::warning('User ' . Auth::user()->rname . ' illegally attempted to delete order ' . $id . '!');
                abort(401, 'You are not authorized to delete that order!');
            }

            $order = Order::findOrFail($id);

            $order->delflg = true;
            $order->save();

            $request->session()->flash('success', 'Deleted order "' . $order->title . '".');

            return redirect('/orders');
    }

    /**
     * Remove multiple orders from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request)
    {
         if (!Auth::user()->checkSecurity('cdorder')) {
            Log::warning('User ' . Auth::user()->rname . ' illegally attempted to bulk delete orders!');
            abort(401, 'You are not authorized to delete orders!');
        }

        $validated = $request->validate([
            'orders'   => 'required|array',
            'orders.*' => 'integer|exists:orders,pkey',
        ]);

        $count = 0;
        foreach ($validated['orders'] as $id) {
            $order = Order::findOrFail($id);
            $order->delflg = true;
            $order->save();
            $count++;
        }

        $request->session()->flash('success', 'Deleted ' . $count . ' order(s).');

        return redirect('/orders');
    }
    /**
     * Update the sort order of orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        if (!Auth::user()->checkSecurity('cmorder')) {
            abort(401, 'Not authorized to reorder orders.');
        }

        $validated = $request->validate([
            'orders'   => 'required|array',
            'orders.*' => 'integer|exists:orders,pkey',
        ]);

        foreach ($validated['orders'] as $index => $id) {
            Order::where('pkey', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
    /**
     * POST /orders/{id}/read
     * Marks a single order as read by the current knight. Idempotent.
     */
    public function markRead(int $id)
    {
        $order = Order::findOrFail($id);

        \App\Model\OrderRead::firstOrCreate([
            'fkeyorder'  => $order->pkey,
            'fkeyknight' => Auth::id(),
        ]);

        return response()->json(['ok' => true]);
    }
}