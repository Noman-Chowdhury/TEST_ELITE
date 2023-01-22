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

Route::get('invoice', [\App\Http\Controllers\HomeController::class, 'invoiceUpdate']);
Route::get('volume', [\App\Http\Controllers\HomeController::class, 'volumeUpdate']);
Route::get('stock', [\App\Http\Controllers\HomeController::class, 'stockUpdate']);

Route::get('dealers', [\App\Http\Controllers\HomeController::class, 'dealerUser']);
Route::get('updateDealer', [\App\Http\Controllers\HomeController::class, 'updateDealer']);
Route::get('updatePainter', [\App\Http\Controllers\HomeController::class, 'updatePainter']);


Route::get('checkData', [\App\Http\Controllers\HomeController::class, 'checkDate']);
Route::get('history', [\App\Http\Controllers\HomeController::class, 'history']);

Route::get('DealerTest',[\App\Http\Controllers\PointController::class, 'getData']);
Route::get('PainterTest',[\App\Http\Controllers\PointController::class, 'PainterTest']);

Route::get('painterRedeemUpdate',[\App\Http\Controllers\PointController::class, 'updateVolumeData']);

Route::get('process', [\App\Http\Controllers\RedeemController::class, 'startRedeem']);


