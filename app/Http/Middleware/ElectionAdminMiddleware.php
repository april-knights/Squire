<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Model\Election;
use App\Model\ElectionAdministrator;

class ElectionAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $knight = auth()->user();

        if (! $knight) {
            return redirect()->route('login');
        }

        $election = Election::active();

        if (! $election) {
            abort(403, 'There is no active election.');
        }

        // Full admins get access only if test mode is enabled
        if ($knight->checkSecurity('admin')) {
            if ($election->admin_test_mode) {
                // Bind both to the request for use in controllers
                $request->attributes->set('active_election', $election);
                $request->attributes->set('ea_record', null);
                $request->attributes->set('is_admin_test', true);
                return $next($request);
            }
            abort(403, 'Admin test mode is not enabled for this election.');
        }

        // Check if the knight is an EA or assistant EA for the active election
        $eaRecord = ElectionAdministrator::where('fkeyelection', $election->pkey)
            ->where('fkeyknight', $knight->pkey)
            ->first();

        if (! $eaRecord) {
            abort(403, 'You are not assigned as Election Administrator for the active election.');
        }

        // Bind to request for use in controllers
        $request->attributes->set('active_election', $election);
        $request->attributes->set('ea_record', $eaRecord);
        $request->attributes->set('is_admin_test', false);

        return $next($request);
    }
}