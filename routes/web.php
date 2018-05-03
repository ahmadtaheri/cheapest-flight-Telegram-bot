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

Route::post('/request','RequestMicroService@interactive');

Route::get('InteractiveResponse','ResponseMicroService@Interactive_Response_With_User');

Route::get('/db','RequestMicroService@db');

Route::get('/response','ResponseMicroService@response');

Route::get('/Cheapest', 'CheapestFlightMicroService@Cheapest');

Route::get('/Notification', 'FlightNotificationMicroService@flightNotification');