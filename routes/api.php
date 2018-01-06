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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('append', 'GestorContenidoController@append');
    Route::post('public', 'GestorContenidoController@public');
    Route::post('cluster', 'GestorContenidoController@cluster');
    Route::post('search', 'GestorBusquedaController@search');
    Route::get('documents', 'GestorContenidoController@documents');
    Route::post('recommendation', 'GestorRecomendacionController@recommendation');
    Route::post('content', 'GestorContenidoController@content');
    Route::patch('settings/profile', 'Settings\UpdateProfile');
    Route::patch('settings/password', 'Settings\UpdatePassword');
});

    Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
