<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    private const EVENT_TYPES = ['reddit', 'internal', 'scheduled'];

    /**
     * List all events.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'livedate');
        $direction = $request->get('direction', 'desc');

        $allowed_sorts = ['pkey', 'title', 'livedate', 'enddate', 'organizer', 'eventtype', 'profileflg', 'activeflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'livedate';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $events = DB::table('event')->orderBy($sort, $direction)->get();

        // Knight counts per event (firstevent FK)
        $counts = DB::table('knight')
            ->select('firstevent', DB::raw('COUNT(*) as cnt'))
            ->where('delflg', 0)
            ->whereNotNull('firstevent')
            ->groupBy('firstevent')
            ->pluck('cnt', 'firstevent');

        $eventTypes = self::EVENT_TYPES;

        return view('admin.events.index', compact('events', 'counts', 'sort', 'direction', 'eventTypes'));
    }

    /**
     * Show a single event.
     */
    public function show($pkey)
    {
        $event = DB::table('event')->where('pkey', $pkey)->first();
        if (!$event) abort(404);

        $knight_count = DB::table('knight')->where('firstevent', $pkey)->where('delflg', 0)->count();

        $lstmdby_name = $event->lstmdby
            ? Knight::withoutGlobalScopes()->where('pkey', $event->lstmdby)->value('dname')
            : null;

        return view('admin.events.show', compact('event', 'knight_count', 'lstmdby_name'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $eventTypes = self::EVENT_TYPES;
        return view('admin.events.create', compact('eventTypes'));
    }

    /**
     * Store a new event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:30',
            'eventdescr' => 'nullable|string|max:500',
            'livedate'   => 'nullable|date',
            'enddate'    => 'nullable|date',
            'organizer'  => 'nullable|string|max:30',
            'eventtype'  => 'required|in:' . implode(',', self::EVENT_TYPES),
            'profileflg' => 'nullable|boolean',
        ]);

        $nextPkey = DB::table('event')->max('pkey') + 1;
        $now      = now();
        $adminId  = auth()->user()->pkey;

        DB::table('event')->insert([
            'pkey'       => $nextPkey,
            'title'      => $request->input('title'),
            'eventdescr' => $request->input('eventdescr'),
            'livedate'   => $request->input('livedate') ?: '0000-00-00',
            'enddate'    => $request->input('enddate') ?: '0000-00-00',
            'organizer'  => $request->input('organizer'),
            'eventtype'  => $request->input('eventtype'),
            'profileflg' => $request->has('profileflg') ? 1 : 0,
            'activeflg'  => 1,
            'delflg'     => 0,
            'crtsetdt'   => $now,
            'crtsetid'   => $adminId,
            'lstmdby'    => $adminId,
            'lstmdts'    => $now,
        ]);

        session()->flash('success', "Event '{$request->input('title')}' created.");
        return redirect("/admin/events/{$nextPkey}");
    }

    /**
     * Show edit form.
     */
    public function edit($pkey)
    {
        $event = DB::table('event')->where('pkey', $pkey)->first();
        if (!$event) abort(404);

        $eventTypes = self::EVENT_TYPES;
        return view('admin.events.edit', compact('event', 'eventTypes'));
    }

    /**
     * Update an event.
     */
    public function update(Request $request, $pkey)
    {
        $event = DB::table('event')->where('pkey', $pkey)->first();
        if (!$event) abort(404);

        $request->validate([
            'title'      => 'required|string|max:30',
            'eventdescr' => 'nullable|string|max:500',
            'livedate'   => 'nullable|date',
            'enddate'    => 'nullable|date',
            'organizer'  => 'nullable|string|max:30',
            'eventtype'  => 'required|in:' . implode(',', self::EVENT_TYPES),
            'profileflg' => 'nullable|boolean',
        ]);

        DB::table('event')->where('pkey', $pkey)->update([
            'title'      => $request->input('title'),
            'eventdescr' => $request->input('eventdescr'),
            'livedate'   => $request->input('livedate') ?: '0000-00-00',
            'enddate'    => $request->input('enddate') ?: '0000-00-00',
            'organizer'  => $request->input('organizer'),
            'eventtype'  => $request->input('eventtype'),
            'profileflg' => $request->has('profileflg') ? 1 : 0,
            'lstmdby'    => auth()->user()->pkey,
            'lstmdts'    => now(),
        ]);

        session()->flash('success', "Event updated.");
        return redirect("/admin/events/{$pkey}");
    }

    /**
     * Soft delete — blocked if knights have this as their first event.
     */
    public function destroy($pkey)
    {
        $event = DB::table('event')->where('pkey', $pkey)->first();
        if (!$event) abort(404);

        $knight_count = DB::table('knight')->where('firstevent', $pkey)->where('delflg', 0)->count();
        if ($knight_count > 0) {
            session()->flash('error', "Cannot delete — {$knight_count} knight(s) have this as their first event.");
            return redirect("/admin/events/{$pkey}");
        }

        DB::table('event')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Event deleted.');
        return redirect('/admin/events');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $event = DB::table('event')->where('pkey', $pkey)->first();
        if (!$event) abort(404);

        DB::table('event')->where('pkey', $pkey)->update([
            'activeflg' => $event->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Event status updated.');
        return redirect("/admin/events/{$pkey}");
    }
}