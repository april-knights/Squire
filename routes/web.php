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
    Route::get('/profile/{rname}/badges', 'ProfileController@badges');
    Route::post('/profile/{rname}/badges', 'ProfileController@updateBadges');
    Route::get('/profile/{rname}/badges', 'ProfileController@badges')->name('profile.badges');
    Route::post('/profile/{rname}/badges', 'ProfileController@updateBadges')->name('profile.badges.update');

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
    Route::get('/orders/create', 'OrdersController@create');
    Route::post('/orders', 'OrdersController@store');
    Route::post('/orders/reorder', 'OrdersController@reorder');
    Route::get('/orders/{id}/edit', 'OrdersController@edit');
    Route::post('/orders/{id}/edit', 'OrdersController@update');
    Route::delete('/orders/{id}', 'OrdersController@destroy');
    Route::delete('/orders', 'OrdersController@bulkDestroy');
    Route::post('/orders/{id}/read', 'OrdersController@markRead');  # READ TRACKING

    # Notifications
    Route::get('/notifications', 'NotificationController@index');
    Route::post('/notifications/read-all', 'NotificationController@markAllRead');
    Route::post('/notifications/{id}/read', 'NotificationController@markRead');

    # Links
    Route::get('/links', 'LinkController@index');

Route::middleware(['admin'])->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/', 'Admin\AdminController@index')->name('admin.index');

        // Knight management
        Route::get('/knights',                'Admin\KnightController@index')->name('admin.knights.index');
        Route::get('/knights/{pkey}',         'Admin\KnightController@show')->name('admin.knights.show');
        Route::get('/knights/{pkey}/edit',    'Admin\KnightController@edit')->name('admin.knights.edit');
        Route::put('/knights/{pkey}/edit',    'Admin\KnightController@update')->name('admin.knights.update');
        Route::post('/knights/{pkey}/toggle', 'Admin\KnightController@toggle')->name('admin.knights.toggle');

    }); // end admin group

}); // end auth group
