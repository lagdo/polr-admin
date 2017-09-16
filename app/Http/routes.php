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

// Homepage
Route::group(array('middleware' => 'user.guest'), function()
{
    Route::get('/', array('as' => 'index', 'uses' => 'HomeController@index'));
    Route::get('logout', array('as' => 'logout', 'uses' => 'UserController@logout'));
});

Route::group(array('middleware' => 'user.check'), function()
{
    Route::get('login', array('as' => 'showLogin', 'uses' => 'UserController@showLogin'));
    // Route::get('signup', array('as' => 'showSignup', 'uses' => 'UserController@showSignup'));
    // Route::get('forgot', array('as' => 'showForgot', 'uses' => 'UserController@showForgot'));

    Route::post('login', array('as' => 'postLogin', 'uses' => 'UserController@postLogin'));
    // Route::post('signup', array('as' => 'postSignup', 'uses' => 'UserController@postSignup'));
    // Route::post('forgot', array('as' => 'postForgot', 'uses' => 'UserController@postForgot'));
});

// Jaxon request processor
Route::post('jaxon', array('as' => 'jaxon', 'uses' => 'AjaxController@process'));
