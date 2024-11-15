<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\classController;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
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
//NEED AUTH GROUP
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/getclass', [ApiController::class, 'getClasses']);
    Route::get('/coachclasses', [ApiController::class, 'apikelasCoach']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Tambahkan rute lain yang memerlukan autentikasi di sini
    Route::get('/logout', [ApiController::class, 'destroyAuth']);
    Route::get('/me', [ApiController::class, 'me']);
    Route::get('/getcoachbookings', [ApiController::class, 'getCoachBookings']);
    //Update
    Route::patch('/user', [ApiController::class, 'updateUser']);
    //POST
    Route::post('/booking', [ApiController::class, 'storeBooking']);
    Route::post('/coachbooking', [ApiController::class, 'storeCoachBooking']);
    Route::post('/attendance', [ApiController::class, 'storeAttendance']);

});

Route::get('/tes', function () {
    dd('test api kocak');
});
//no need sanctum
Route::get('/popularcategory', [ApiController::class, 'getPopularCategory']);
Route::get('/gettime', [ApiController::class, 'getAvailableTimes']);
Route::get('/classes', [ApiController::class, 'apikelas']);
Route::get('/getpopularclasses', [ApiController::class, 'getPopularClasses']);
Route::get('/member', [ApiController::class, 'apiMember']);
Route::get('/coach', [ApiController::class, 'apiCoach']);
Route::get('/booking', [ApiController::class, 'apiBooking']);
Route::get('/coachbooking', [ApiController::class, 'apiCoachBooking']);
Route::get('/attendance', [ApiController::class, 'apiAttendance']);
Route::post('/login', [ApiController::class, 'loginStoreAuth']);
Route::post('/register', [ApiController::class, 'registerStoreAuth']);
