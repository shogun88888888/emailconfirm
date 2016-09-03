<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home',
[
    'as' => 'home',
    'uses' => 'HomeController@index'
]);

Route::get('user/activation/{token}', 'Auth\LoginController@activateUser')->name('user.activate');

// Begin Profile Routes
Route::any('api/profile', 'ApiController@profileData');
Route::any('api/profile-vue', 'ApiController@profileVueData');

Route::resource('profile', 'ProfileController');
// End Profile Routes