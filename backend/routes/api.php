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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// 注册相关
Route::post('register', 'PassportController@register');
Route::post('register_by_invitation', 'PassportController@register_by_invitation');
Route::post('login', 'PassportController@login')->name('login');
Route::post('logout', 'PassportController@logout')->name('logout');

Route::resource('shared','ShareEventController')->middleware('auth:api');
Route::get('shared/{share_event_id}/{t}/{token}','ShareEventController@show');
Route::get('shared/{shared}',function(){
    abort(404);
});

Route::delete('disk','ItemController@destroy')->middleware('auth:api');
Route::resource('disk','ItemController')->middleware('auth:api');
Route::delete('disk/{disk}',function() {
    abort(404);
} )->middleware('auth:api');
Route::get('auth/getKey','ItemController@getKey')->middleware('auth:api');
