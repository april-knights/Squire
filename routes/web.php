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

    # Profile
    Route::get('/profile/new', 'ProfileController@create');
    Route::get('/profile/{rname}', 'ProfileController@show');
    Route::get('/profile/{rname}/edit', 'ProfileController@edit');

    Route::post('/profile/new', 'ProfileController@store');
    Route::post('/profile/{rname}/edit', 'ProfileController@update');

    # Battalion
    Route::get('/battalion', 'BattalionController@index');
    Route::get('/battalion/{alias}', 'BattalionController@show');
    Route::get('/battalion/{alias}/members', 'BattalionController@members');

    # Division
    Route::get('/division', 'DivisionController@index');
    Route::get('/division/{alias}', 'DivisionController@show');
    Route::get('/division/{alias}/members', 'DivisionController@members');

    # Orders
    Route::get('/orders', 'OrdersController@index');

    # Links
    Route::get('/links', 'LinkController@index');
});
