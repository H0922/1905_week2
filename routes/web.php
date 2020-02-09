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
    return view('welcome');
});
Route::get('/phpinfo', function () {
    phpinfo();
});
//支付
Route::get('server/goods','Server\ServerController@goods');
Route::get('server/good','Server\ServerController@good');
Route::get('server/rsa','Server\ServerController@rsa');
Route::get('sign/sign1','Sign\SignController@sign1');
Route::get('sign/aes','Sign\SignController@aes');
Route::get('rsa2','Sign\SignController@rsa2');

Route::get('server/curlpost','Server\ServerController@curlpost');
Route::get('server/curlfile','Server\ServerController@curlfile');
Route::get('server/sing','Server\ServerController@sing');
Route::get('server/sign2','Server\ServerController@sign2');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
