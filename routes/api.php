<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', 'API\LoginController@register');
Route::post('login', 'API\LoginController@login');
  
Route::group(array('middleware' => ['auth:api']), function(){
	Route::get('logout', 'API\LoginController@logout');
	Route::post('details', 'API\LoginController@details');
	});

// Forgot Password update
Route::post('forgotPassword', 'API\LoginController@forgotPassword');
// Forgot Password Confirmation
Route::get('forgotPasswordConfirm/{userId}/{passwordResetCode}', 'API\LoginController@getForgotPasswordConfirm');
Route::post('forgotPasswordConfirm', 'API\LoginController@postForgotPasswordConfirm');
