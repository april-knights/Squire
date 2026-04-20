<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'active'         => Knight::withoutGlobalScopes()->where('activeflg', 1)->where('delflg', 0)->count(),
            'inactive'       => Knight::withoutGlobalScopes()->where('activeflg', 0)->where('delflg', 0)->count(),
            'deleted'        => Knight::withoutGlobalScopes()->where('delflg', 1)->count(),
            'recent_logins'  => Knight::withoutGlobalScopes()->where('activeflg', 1)->where('delflg', 0)
                                    ->where('last_login', '>=', Carbon::now()->subDays(30))
                                    ->count(),
        ];

        return view('admin.index', compact('stats'));
    }
}