<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\StudentParentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AuthController;

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

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

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
Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('student-parents', StudentParentController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('trips', TripController::class);

// Custom routes for trips
Route::group(['prefix' => 'trips', 'as' => 'trips.'], function () {
    Route::post('/', [TripController::class, 'store'])->name('store');
    Route::get('/', [TripController::class, 'index'])->name('index');
    Route::post('{id}/assign-students', [TripController::class, 'assignStudents'])->name('assign-students');
    Route::get('{id}/points', [TripController::class, 'getPoints'])->name('points');
    Route::get('{id}/students', [TripController::class, 'getStudents'])->name('students');
    Route::get('{trip_id}/points/{point_id}/students', [TripController::class, 'getPointStudents'])->name('point-students');
});
