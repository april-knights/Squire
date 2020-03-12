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

Route::get('/', function () {
    return view('home');
});

Route::view('/login', 'login');

# Profile
Route::get('/profile/{rname}', 'ProfileController@show');


# Battalion
Route::get('/battalion', 'BattalionController@index');
Route::get('/battalion/{name}', 'BattalionController@show');


# Signin
Route::get('/login/reddit', 'LoginController@redirectToProvider');
Route::get('/login/reddit/callback', 'LoginController@handleProviderCallback');
