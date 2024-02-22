<?php

namespace App\Http\Controllers;

use App\Models\Knight;
use App\Models\Rank;
use Auth;
use Socialite;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Reddit authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider()
    {
        if ($this->isTesting()) {
            return redirect()->route('loginCallback');
        }
        return Socialite::driver('reddit')->redirect();
    }

    /**
     * Obtain the user information from Reddit.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback()
    {
        // Grants access to the grandmaster account to everyone when locally testing. Be careful.
        if ($this->isTesting()) {
            $knight = Knight::whereRnk(Rank::GRANDMASTER_RANK_ID)->first();
        } else {
            $reddit_user = Socialite::driver('reddit')->user();

            // Check if user exists and has not been deleted
            $knight = Knight::where('rname', $reddit_user->getNickname())->first();
        }

        if(!$knight) {
            return redirect()->to('/login')->with('error', 'User not registered.');
        }

        // Log in user, handling session etc.
        Auth::login($knight);

        return redirect()->to('/');
    }

    /**
     * @return bool Whether the site is being tested locally.
     */
    private function isTesting() {
        return config('app.env') === 'local' && config('services.reddit.client_secret') === 'CHANGEME';
    }
}
