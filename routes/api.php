<?php

use App\Http\Controllers\SensorDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/parkingspot/store', [SensorDataController::class,'store'])->name('parkingspot.store');
Route::get('/parkingspot/get', [SensorDataController::class,'get'])->name('parkingspot.get');
Route::post('/parkingspot/reserve/{id}/{userid}', [SensorDataController::class,'reserve'])->name('parkingspot.reserve');
Route::post('/parkingspot/cancel/{userid}', [SensorDataController::class,'cancelReservation'])->name('parkingspot.cancel');
Route::post('/parkingspot/updateavailability/{sensor_id}/{availability}', [SensorDataController::class,'updateAvailability'])->name('parkingspot.sensor.update');
