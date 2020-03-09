<?php

namespace App\Http\Controllers;

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
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('reddit')->user();

        dd($user);

        return redirect()->to("/");
    }
}
