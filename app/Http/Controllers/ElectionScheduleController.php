<?php

namespace App\Http\Controllers;

use App\Services\ElectionSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElectionScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Admin, Grandmaster and Archmage manage the schedule.
        // extendVoting is the Election Administrator's action: its route sits inside the
        // election.admin group, which supplies the election and EA record.
        $this->middleware(function ($request, $next) {
            if (! ElectionSchedule::canManage(auth()->user())) {
                abort(403, 'Only an Admin, the Grandmaster or an Archmage can manage the election schedule.');
            }
            return $next($request);
        })->except('extendVoting');
    }

    public function index()
    {
        $election = ElectionSchedule::currentElection();

        if (! $election) {
            return redirect('/')->with('error', 'There is no election set up yet.');
        }

        $schedule  = ElectionSchedule::forElection($election);
        $suggested = ElectionSchedule::suggest($election);
        $locked    = $election->isComplete();

        return view('election.schedule', compact('election', 'schedule', 'suggested', 'locked'));
    }

    public function update(Request $request)
    {
        $election = ElectionSchedule::currentElection();

        if (! $election) {
            return redirect('/')->with('error', 'There is no election set up yet.');
        }

        if ($election->isComplete()) {
            return back()->with('error', 'This election is complete; its schedule is locked.');
        }

        $result = ElectionSchedule::save(
            $election,
            $request->only(array_keys(ElectionSchedule::FIELDS)),
            auth()->user()->pkey
        );

        if (! $result['ok']) {
            return back()->withInput()->with('error', $result['error']);
        }

        $message = $result['changed']
            ? 'Schedule saved (' . $result['changed'] . ' change' . ($result['changed'] === 1 ? '' : 's') . ' logged).'
            : 'No changes.';

        return redirect()->route('election.schedule')->with('success', $message);
    }

    public function registrationThread(Request $request)
    {
        $election = ElectionSchedule::currentElection();

        if (! $election) {
            return redirect('/')->with('error', 'There is no election set up yet.');
        }

        if ($election->isComplete()) {
            return back()->with('error', 'This election is complete.');
        }

        $result = ElectionSchedule::saveRegistrationThread(
            $election,
            $request->input('registration_thread_url'),
            auth()->user()->pkey
        );

        if (! $result['ok']) {
            return back()->withInput()->with('error', $result['error']);
        }

        return redirect()->route('election.schedule')->with('success', $result['message']);
    }

    public function extendVoting(Request $request)
    {
        $election    = $request->attributes->get('active_election');
        $eaRecord    = $request->attributes->get('ea_record');
        $isAdminTest = $request->attributes->get('is_admin_test', false);

        if ($isAdminTest || ! $eaRecord || $eaRecord->is_assistant) {
            abort(403, 'Only the full Election Administrator can extend voting.');
        }

        $v = Validator::make($request->all(), [
            'extend_until' => 'required|string|max:32',
            'note'         => 'nullable|string|max:500',
        ]);

        if ($v->fails()) {
            return back()->with('error', $v->errors()->first());
        }

        $result = ElectionSchedule::extendVoting(
            $election,
            $request->input('extend_until'),
            $request->input('note'),
            auth()->user()->pkey
        );

        if (! $result['ok']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Voting close extended. The extension is recorded in the phase log.');
    }
}