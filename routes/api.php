<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->group(function () {
	
	Route::get('/User', function (Request $request) {return $request->user();} );
	 
	Route::get('User/Index', 'Api\UserApiController@index');
	
	Route::get('User/Profile', 'Api\UserApiController@profile');	 
	
	Route::post('User/Profile', 'Api\UserApiController@profileUpdate');	
	
	Route::post('User/JoinTelegram', 'Api\UserApiController@joinTelegram');
	
	Route::post('User/JoinInstagram', 'Api\UserApiController@joinInstagram');
	
	Route::post('User/WatchVideo', 'Api\UserApiController@watchVideo');
	
		
	Route::get('Board/Top', 'Api\UserApiController@topList');
		
	Route::get('Board/Active', 'Api\UserApiController@activeList');
	
	Route::get('Store/Index', 'Api\StoreApiController@index');
	
	


});

Route::resource('auth/Register', 'Api\RegisterApiController');

Route::resource('auth/Login', 'Api\LoginApiController');

Route::post('auth/GetGuestToken', 'Api\LoginApiController@getGuestToken');

Route::post('auth/GetGuestToken', 'Api\LoginApiController@getGuestToken');

