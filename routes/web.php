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
    return redirect('login');
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/chat/{id}', 'HomeController@chat')->name('chat');
Route::get('/chatGet/{id}', 'HomeController@chatGet')->name('chat');
Route::post('/chatSend/{id}', 'HomeController@chatSend')->name('chat');

