<?php

use App\Http\Controllers\Api\CheckInOutController;
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
});
Route::group(['middleware' => ['auth:sanctum','ability:token:create']], function () {
    Route::get('/code/{user:chip_id}', [RFIDController::class,'getToken']);
    Route::get('/chip_id/{chip_id}', [RFIDController::class,'annouceChipID']);
    Route::post('/device/register', [RFIDController::class,'registerDeviceRegister']);
});

