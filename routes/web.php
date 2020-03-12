<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

## Public routes

Route::view('/login', 'login')->name('login');
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});

# Signin
Route::get('/login/reddit', 'LoginController@redirectToProvider');
Route::get('/login/reddit/callback', 'LoginController@handleProviderCallback');


## Internal routes
Route::middleware(['auth'])->group(function () {
    Route::view('/', 'home');
    Route::view('/links', 'links');
    Route::view('/orders', 'orders');

    # Profile
    Route::get('/profile/{rname}', 'ProfileController@show');

    # Battalion
    Route::get('/battalion', 'BattalionController@index');
    Route::get('/battalion/{name}', 'BattalionController@show');
});
