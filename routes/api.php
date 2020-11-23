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

JsonApi::register('default')->routes(function ($api) {
    $api->resource('xes')->relationships(function ($relations) {
        $relations->hasMany('ys');
        $relations->hasMany('zs');
    });
    $api->resource('ys')->relationships(function ($relations) {
        $relations->hasMany('xes');
    });
    $api->resource('zs')->relationships(function ($relations) {
        $relations->hasMany('xes');
    });
});
