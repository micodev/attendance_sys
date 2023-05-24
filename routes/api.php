<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Models\Attendance;

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


Route::post('login', [AuthController::class,'login']);
Route::post('create_user', [AuthController::class,'create_new_user']);
Route::get('users', [AuthController::class,'index']);
Route::put('users', [AuthController::class,'update']);

Route::get('user_attendances', [AttendanceController::class,'user_index']);
Route::get('attendances', [AttendanceController::class,'index']);

Route::post('internal/attendance', [AttendanceController::class,'create']);
Route::post('check', [AttendanceController::class,'check']);
//logs

//settings
Route::get('settings', [SettingsController::class,'index']);
Route::put('settings', [SettingsController::class,'update']);
//attachments
