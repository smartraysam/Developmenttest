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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// this is an api route to retun payment data into json
// calling from postman  .. baseurl/api/get/payment
// method is GET
// parament to set are.. start_date  and end_date   value in YYYY-MM-DD
Route::get('/get/payments', [App\Http\Controllers\PaymentsController::class, 'RevenueTransactions']);