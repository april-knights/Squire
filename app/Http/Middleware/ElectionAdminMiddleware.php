<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Model\Election;
use App\Model\ElectionAdministrator;

class ElectionAdminMiddleware
{
    /**
     * Routes that stay available after an election is complete:
     * read-only review, the audit, and the key archive.
     */
    const COMPLETE_ALLOWED_ROUTES = [
        'election.dashboard',
        'election.voters',
        'election.results',
        'election.audit',
        'election.archive-key',
    ];

    public function handle(Request $request, Closure $next)
    {
        $knight = auth()->user();

        if (! $knight) {
            return redirect()->route('login');
        }

        // Current election: the active one, otherwise the most recent
        // (so the EA can still reach a just-completed election).
        $election = Election::active()
            ?: Election::orderByDesc('election_year')->orderByDesc('pkey')->first();

        if (! $election) {
            return redirect('/')->with('error', 'There is no election set up right now.');
        }

        // An EA or assistant EA record always wins, even for an Admin who holds that role.
        $eaRecord = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->first();

        $isAdmin     = (int) $knight->security === 1;
        $isAdminTest = false;

        if (! $eaRecord) {
            if ($isAdmin && $election->admin_test_mode) {
                $isAdminTest = true;
            } elseif ($isAdmin) {
                return redirect()
                    ->route('admin.elections.show', $election->pkey)
                    ->with('error', 'Turn on Admin test mode for this election to view the EA dashboard.');
            } else {
                return redirect('/')
                    ->with('error', 'You are not assigned as Election Administrator for the current election.');
            }
        }

        // After completion only review, audit and key archive remain.
        if ($election->isComplete() && ! $request->routeIs(...self::COMPLETE_ALLOWED_ROUTES)) {
            return redirect()
                ->route('election.dashboard')
                ->with('error', 'This election is complete. Only results, the audit and the key archive remain available.');
        }

        $request->attributes->set('active_election', $election);
        $request->attributes->set('ea_record', $eaRecord);
        $request->attributes->set('is_admin_test', $isAdminTest);

        return $next($request);
    }
}