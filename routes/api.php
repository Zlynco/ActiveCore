<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\classController;
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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Tambahkan rute lain yang memerlukan autentikasi di sini
});

Route::get('/posts', function () {
    dd('test api kocak');
});
Route::get('/classes', [ApiController::class, 'apikelas']);
Route::get('/member', [ApiController::class, 'apiMember']);
Route::get('/coach', [ApiController::class, 'apiCoach']);
Route::get('/booking', [ApiController::class, 'apiBooking']);
Route::get('/coachbooking', [ApiController::class, 'apiCoachBooking']);
Route::get('/attendance', [ApiController::class, 'apiAttendance']);
