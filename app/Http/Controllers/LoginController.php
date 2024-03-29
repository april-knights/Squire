<?php

namespace App\Http\Controllers;

use App\Model\Knight;
use Auth;
use Socialite;

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
    public function handleProviderCallback()
    {
        $reddit_user = Socialite::driver('reddit')->user();

        // Check if user exists and has not been deleted
        $knight = Knight::where('rname', $reddit_user->getNickname())->first();

        if(!$knight) {
            return redirect()->to('/login')->with('error', 'User not registered.');
        }

        // Log in user, handling session etc.
        Auth::login($knight);

        return redirect()->to('/');
    }
}
