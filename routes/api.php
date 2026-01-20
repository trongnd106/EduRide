<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\StudentParentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
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

Route::get('students/all', [StudentController::class, 'all'])->name('students.all');
Route::apiResource('students', StudentController::class);
Route::apiResource('schools', SchoolController::class);
Route::get('drivers/all', [DriverController::class, 'all'])->name('drivers.all');
Route::apiResource('drivers', DriverController::class);
Route::get('vehicles/all', [VehicleController::class, 'all'])->name('vehicles.all');
Route::get('vehicles/user-trips', [VehicleController::class, 'getAssistantVehicles'])->middleware('auth:api')->name('vehicles.my-trips');
Route::apiResource('vehicles', VehicleController::class);
Route::get('student-parents/all', [StudentParentController::class, 'all'])->name('student-parents.all');
Route::apiResource('student-parents', StudentParentController::class);
Route::apiResource('users', UserController::class);
// Custom routes for trips
Route::group(['prefix' => 'trips', 'as' => 'trips.'], function () {
    Route::get('user-trips', [TripController::class, 'getUserTrips'])->middleware('auth:api')->name('user.my-trips');
    Route::post('check-in', [TripController::class, 'checkIn'])->name('check-in');
    Route::post('{id}/start', [TripController::class, 'startTrip'])->name('start');
    Route::post('{id}/end', [TripController::class, 'endTrip'])->name('end');
    Route::post('{id}/assign-students', [TripController::class, 'assignStudents'])->name('assign-students');
    Route::post('{id}/assign-point-students', [TripController::class, 'assignPointStudents'])->name('assign-point-students');
    Route::get('{id}/points', [TripController::class, 'getPoints'])->name('points');
    Route::get('{id}/students', [TripController::class, 'getStudents'])->name('students');
    Route::get('{trip_id}/points/{point_id}/students', [TripController::class, 'getPointStudents'])->name('point-students');
});

Route::apiResource('trips', TripController::class);
Route::get('points/all', [PointController::class, 'all'])->name('points.all');
Route::apiResource('points', PointController::class);

Route::prefix('notifications')->middleware('auth:api')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/{id}', [NotificationController::class, 'show']);
    Route::put('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::put('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/read/all', [NotificationController::class, 'deleteAllRead']);
    Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);

    // Test route (only for development)
    if (config('app.env') !== 'production') {
        Route::post('/test-send', [NotificationController::class, 'testSendNotification']);
    }
});
