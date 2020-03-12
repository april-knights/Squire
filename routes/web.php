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

Route::view('/', 'home')->middleware('auth');
Route::view('/links', 'links')->middleware('auth');;

# Profile
Route::get('/profile/{rname}', 'ProfileController@show')->middleware('auth');;


# Battalion
Route::get('/battalion', 'BattalionController@index')->middleware('auth');;
Route::get('/battalion/{name}', 'BattalionController@show')->middleware('auth');;
