<?php

use App\Http\Controllers\Api\CheckInOutController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GreenkeepingController;
use App\Http\Controllers\Api\ShiftEnrollmentController;
use App\Http\Controllers\ArbeitszeitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RFIDController;

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

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/chip/{user:chip_id}', CheckInOutController::class);
    Route::get('overtime',[ArbeitszeitController::class,'getOvertime']);
    Route::get('gastro/{date}',[ArbeitszeitController::class,'getGastroTime']);

    // Shift Enrollment API
    Route::get('shifts', [ShiftEnrollmentController::class, 'index']);
    Route::get('shifts/{shift}', [ShiftEnrollmentController::class, 'show']);
    Route::post('shifts/{shift}/enroll', [ShiftEnrollmentController::class, 'enroll']);
    Route::delete('shifts/{shift}/unenroll', [ShiftEnrollmentController::class, 'unenroll']);
    Route::post('shifts/{shift}/decline', [ShiftEnrollmentController::class, 'decline']);
    Route::delete('shifts/{shift}/decline', [ShiftEnrollmentController::class, 'removeDecline']);

    // Dashboard API Endpoints
    Route::get('dashboard/team-status', [DashboardController::class, 'teamStatus']);
    Route::post('dashboard/job-group-shifts', [DashboardController::class, 'jobGroupShifts']);
});
Route::group(['middleware' => ['auth:sanctum','ability:token:create']], function () {
    Route::get('/code/{user:chip_id}', [RFIDController::class,'getToken']);
    Route::get('/chip_id/{chip_id}', [RFIDController::class,'annouceChipID']);
    Route::post('/device/register', [RFIDController::class,'registerDeviceRegister']);
});

// Greenkeeper-Orga: Abwesenheiten (ohne Grund) und Stempelstatus heute.
// Token mit Fähigkeit greenkeeping:read über `php artisan greenkeeping:token`.
Route::group(['middleware' => ['auth:sanctum', 'ability:greenkeeping:read', 'throttle:60,1']], function () {
    Route::get('greenkeeping/verfuegbarkeit', [GreenkeepingController::class, 'verfuegbarkeit']);
});
