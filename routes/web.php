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

Route::get('/', 'HomeController@index');
Route::get('export', 'HomeController@exportPayment');

Route::get('qurban/report/{id}', 'QurbanController@orderReport');
Route::get('qurban/report/generate/{id}', 'QurbanController@orderReportGenerate');
Route::get('qurban/report/video/{id}', 'QurbanController@orderReportVideo');
Route::get('qurban/report/image/{id}', 'QurbanController@orderReportImage');
Route::get('qurban/location/report/{id}', 'QurbanController@locationReport');

Route::get('email-test', 'HomeController@emailTest');
