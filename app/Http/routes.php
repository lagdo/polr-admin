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
    Route::get('/logout', array('as' => 'logout', 'uses' => 'UserController@logout'));

    // Pagination routes (for Datatables)
    Route::group(['prefix' => '/api/v2'], function ($app) {
        Route::get('get_admin_users', ['as' => 'api_get_admin_users', 'uses' => 'PaginationController@paginateAdminUsers']);
        Route::get('get_admin_links', ['as' => 'api_get_admin_links', 'uses' => 'PaginationController@paginateAdminLinks']);
        Route::get('get_user_links', ['as' => 'api_get_user_links', 'uses' => 'PaginationController@paginateUserLinks']);
    });

    // Link stats
    Route::get('stats/{short_url}', ['uses' => 'StatsController@displayStats']);
});

Route::group(array('middleware' => 'user.check'), function()
{
    Route::get('/login', array('as' => 'showLogin', 'uses' => 'UserController@showLogin'));
    if(true /*env('POLR_ALLOW_ACCT_CREATION')*/)
    {
        Route::get('/signup', array('as' => 'showSignup', 'uses' => 'UserController@showSignup'));
    }
    Route::get('/activate/{username}/{recoveryKey}', ['as' => 'activate', 'uses' => 'UserController@activate']);
    Route::get('/password/lost', array('as' => 'showLostPassword', 'uses' => 'UserController@showLostPassword'));
    Route::get('/password/reset/{username}/{recoveryKey}',
        ['as' => 'getResetPassword', 'uses' => 'UserController@resetPassword']);

    Route::post('login', array('as' => 'postLogin', 'uses' => 'UserController@postLogin'));
    if(true /*env('POLR_ALLOW_ACCT_CREATION')*/)
    {
        Route::post('/signup', array('as' => 'postSignup', 'uses' => 'UserController@postSignup'));
    }
    Route::post('/password/lost', array('as' => 'postLostPassword', 'uses' => 'UserController@postLostPassword'));
    Route::post('/password/reset/{username}/{recoveryKey}',
        ['as' => 'postResetPassword', 'uses' => 'UserController@resetPassword']);
});

// Jaxon request processor
Route::post('jaxon', array('as' => 'jaxon', 'uses' => 'AjaxController@process'));
