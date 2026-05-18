<?php

namespace App\Http\Controllers;

use App\Model\Knight;
use Auth;
use Socialite;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Reddit authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('reddit')->redirect();
    }

    /**
     * Obtain the user information from Reddit.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(Request $request)
    {
        // AKSquire2 bot authorization flow
        if ($request->state && str_starts_with($request->state, 'aksquire2_auth_')) {
            $success = app(\App\Services\RedditService::class)->storeTokens(
                $request->code,
                config('services.reddit.redirect')
            );

            if (! $success) {
                return redirect()->route('admin.elections.settings')
                    ->with('error', 'AKSquire2 authorization failed. Check logs.');
            }

            return redirect()->route('admin.elections.settings')
                ->with('success', 'AKSquire2 authorized successfully.');
        }

        // Standard knight login flow
        $reddit_user = Socialite::driver('reddit')->user();

        $knight = Knight::where('rname', $reddit_user->getNickname())->first();
        if (!$knight) {
            return redirect()->to('/login')->with('error', 'User not registered.');
        }

        Auth::login($knight);
        Knight::where('pkey', $knight->pkey)->update(['last_login' => now()]);
        return redirect()->intended('/');
    }
}
