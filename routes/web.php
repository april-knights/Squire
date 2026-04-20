<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
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
    Route::post('/orders/{id}/read', 'OrdersController@markRead');

    # Notifications
    Route::get('/notifications', 'NotificationController@index');
    Route::post('/notifications/read-all', 'NotificationController@markAllRead');
    Route::post('/notifications/{id}/read', 'NotificationController@markRead');

    # Links
    Route::get('/links', 'LinkController@index');

# Admin
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/', 'Admin\AdminController@index')->name('admin.index');

        # Knight management
        Route::get('/knights/search',            'Admin\DivisionController@knightSearch')->name('admin.knights.search');
        Route::get('/knights',                   'Admin\KnightController@index')->name('admin.knights.index');
        Route::get('/knights/{pkey}',            'Admin\KnightController@show')->name('admin.knights.show');
        Route::get('/knights/{pkey}/edit',       'Admin\KnightController@edit')->name('admin.knights.edit');
        Route::put('/knights/{pkey}/edit',       'Admin\KnightController@update')->name('admin.knights.update');
        Route::post('/knights/{pkey}/toggle',    'Admin\KnightController@toggle')->name('admin.knights.toggle');

        # Security profiles
        Route::get('/security',                  'Admin\SecurityController@index')->name('admin.security.index');
        Route::get('/security/create',           'Admin\SecurityController@create')->name('admin.security.create');
        Route::post('/security',                 'Admin\SecurityController@store')->name('admin.security.store');
        Route::get('/security/{pkey}',           'Admin\SecurityController@show')->name('admin.security.show');
        Route::get('/security/{pkey}/edit',      'Admin\SecurityController@edit')->name('admin.security.edit');
        Route::put('/security/{pkey}/edit',      'Admin\SecurityController@update')->name('admin.security.update');
        Route::post('/security/{pkey}/delete',   'Admin\SecurityController@destroy')->name('admin.security.destroy');
        Route::post('/security/{pkey}/toggle',   'Admin\SecurityController@toggle')->name('admin.security.toggle');

        # Rank management
        Route::get('/ranks',                     'Admin\RankController@index')->name('admin.ranks.index');
        Route::get('/ranks/create',              'Admin\RankController@create')->name('admin.ranks.create');
        Route::post('/ranks',                    'Admin\RankController@store')->name('admin.ranks.store');
        Route::get('/ranks/{pkey}',              'Admin\RankController@show')->name('admin.ranks.show');
        Route::get('/ranks/{pkey}/edit',         'Admin\RankController@edit')->name('admin.ranks.edit');
        Route::put('/ranks/{pkey}/edit',         'Admin\RankController@update')->name('admin.ranks.update');
        Route::post('/ranks/{pkey}/delete',      'Admin\RankController@destroy')->name('admin.ranks.destroy');
        Route::post('/ranks/{pkey}/toggle',      'Admin\RankController@toggle')->name('admin.ranks.toggle');

        # Division management
        Route::get('/divisions',                                    'Admin\DivisionController@index')->name('admin.divisions.index');
        Route::get('/divisions/create',                             'Admin\DivisionController@create')->name('admin.divisions.create');
        Route::post('/divisions',                                   'Admin\DivisionController@store')->name('admin.divisions.store');
        Route::get('/divisions/{pkey}',                             'Admin\DivisionController@show')->name('admin.divisions.show');
        Route::get('/divisions/{pkey}/edit',                        'Admin\DivisionController@edit')->name('admin.divisions.edit');
        Route::put('/divisions/{pkey}/edit',                        'Admin\DivisionController@update')->name('admin.divisions.update');
        Route::post('/divisions/{pkey}/delete',                     'Admin\DivisionController@destroy')->name('admin.divisions.destroy');
        Route::post('/divisions/{pkey}/toggle',                     'Admin\DivisionController@toggle')->name('admin.divisions.toggle');
        Route::post('/divisions/{pkey}/members',                    'Admin\DivisionController@addMember')->name('admin.divisions.members.add');
        Route::post('/divisions/{pkey}/members/{pivotPkey}/remove', 'Admin\DivisionController@removeMember')->name('admin.divisions.members.remove');

    }); // end admin group

}); // end auth group