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
    Route::view('/', 'home')->name('home');

    # Profile
    Route::get('/profile/new', 'ProfileController@create');
    Route::get('/profile/{rname}', 'ProfileController@show')->name('profile');
    Route::get('/profile/{rname}/edit', 'ProfileController@edit');
    Route::post('/profile/new', 'ProfileController@store');
    Route::post('/profile/{rname}/edit', 'ProfileController@update');
    Route::delete('profile/{rname}', 'ProfileController@destroy');

    # Battalion
    Route::get('/battalion', 'BattalionController@index');
    Route::get('/battalion/{alias}', 'BattalionController@show');
    Route::get('/battalion/{alias}/members', 'BattalionController@members');
    Route::get('/battalion/{alias}/edit', 'BattalionController@edit');
    Route::post('/battalion/{alias}/edit', 'BattalionController@update');
    Route::delete('/battalion/{alias}', 'BattalionController@destroy');

    # Division
    Route::get('/division', 'DivisionController@index');
    Route::get('/division/{alias}', 'DivisionController@show');
    Route::get('/division/{alias}/members', 'DivisionController@members');

    # Orders
    Route::get('/orders', 'OrdersController@index');
    Route::get('/orders/create', 'OrdersController@create');       # NEW
    Route::post('/orders', 'OrdersController@store');              # NEW
    Route::post('/orders/reorder', 'OrdersController@reorder');        # REORDER
    Route::get('/orders/{id}/edit', 'OrdersController@edit');      # NEW
    Route::post('/orders/{id}/edit', 'OrdersController@update');   # NEW
    Route::delete('/orders/{id}', 'OrdersController@destroy');     # NEW
    Route::delete('/orders', 'OrdersController@bulkDestroy');      # BULK DELETE

    # Links
    Route::get('/links', 'LinkController@index');
});
