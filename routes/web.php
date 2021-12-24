<?php

use Illuminate\Support\Facades\Route;

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

//paystack
Route::post('/pay', [App\Http\Controllers\PaymentsController::class, 'redirectToGateway'])->name('pay');
// The callback url after a payment
Route::get('/payment/callback',  [App\Http\Controllers\PaymentsController::class, 'handleGatewayCallback']);
Route::get('/payment',  [App\Http\Controllers\PaymentsController::class, 'paynow']);

//flutterwave
Route::post('/rave', [App\Http\Controllers\PaymentsController::class, 'initialize'])->name('rave');
// The callback url after a payment
Route::get('/rave/callback', [App\Http\Controllers\PaymentsController::class, 'callback'])->name('callback');
