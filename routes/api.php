<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DriverController;

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

Route::get('/', function () {
    return response()->json(['status' => 'OK', 'message' => 'API is working!']);
});

// Route::group(['prefix' => 'students', 'as' => 'student'], function () {
//     Route::get('/{id}', [StudentController::class, 'show'])->name('show');
//     Route::get('/', [StudentController::class, 'index'])->name('index');
//     Route::post('/', [StudentController::class, 'store'])->name('store');
//     Route::put('/{id}', [StudentController::class, 'update'])->name('update');
//     Route::delete('/{id}', [StudentController::class, 'destroy'])->name('destroy');
// });
Route::apiResource('students', StudentController::class);
Route::apiResource('schools', SchoolController::class);
Route::apiResource('drivers', DriverController::class);
