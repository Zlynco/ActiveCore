<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SearchController;
use BaconQrCode\Encoder\QrCode;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

// Rute untuk menangani login secara POST
Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::get('/', function () {
    return redirect()->route('login');
});
Route::view('/kelas', 'kelas')->name('kelas');
Route::view('/coach', 'coach')->name('coach');
Route::view('/member', 'member')->name('member');
Route::view('/booking', 'booking')->name('booking');
Route::view('/pending', 'pending')->name('pending');



Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    // Rute untuk manage users
    Route::get('/admin/users', [AdminController::class, 'manageUsers'])->name('admin.user');

    // Rute untuk edit, update, dan delete user
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.user.edit');
    Route::post('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');

    // Rute untuk edit, update, dan delete coach
    Route::get('/admin/coaches/{id}/edit', [AdminController::class, 'editCoach'])->name('admin.coach.edit');
    Route::put('/admin/coaches/{id}', [AdminController::class, 'updateCoach'])->name('admin.coach.update');
    Route::delete('/admin/coaches/{id}', [AdminController::class, 'deleteCoach'])->name('admin.coach.delete');
    Route::put('/admin/coaches/{id}/approve', [AdminController::class, 'approveCoach'])->name('admin.coach.approve');
    Route::put('/admin/coaches/{id}/reject', [AdminController::class, 'rejectCoach'])->name('admin.coach.reject');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Rute untuk manage kelas
        Route::get('/kelas', [AdminController::class, 'manageClasses'])->name('kelas');
        Route::get('/class/create', [AdminController::class, 'createClass'])->name('classes.create');
        Route::post('/class', [AdminController::class, 'storeClass'])->name('classes.store');
        Route::get('/class/edit/{id}', [AdminController::class, 'editClass'])->name('classes.edit');
        Route::put('/class/update/{id}', [AdminController::class, 'updateClass'])->name('classes.update');
        Route::delete('/class/delete/{id}', [AdminController::class, 'deleteClass'])->name('classes.delete');
        // Routes untuk booking
        Route::get('booking', [AdminController::class, 'manageBooking'])->name('booking');
        Route::get('bookings/create', [AdminController::class, 'createBooking'])->name('bookings.create');
        Route::post('bookings', [AdminController::class, 'storeBooking'])->name('bookings.store');
        Route::get('bookings/{id}/edit', [AdminController::class, 'editBooking'])->name('bookings.edit');
        Route::put('bookings/{id}', [AdminController::class, 'updateBooking'])->name('bookings.update');
        Route::delete('bookings/{id}', [AdminController::class, 'destroyBooking'])->name('bookings.destroy');
        Route::post('/bookings/{id}/scan', [AdminController::class, 'scanQRCodeBook']);

        // Route untuk menampilkan halaman pembayaran
        Route::get('/payment/{booking}', [AdminController::class, 'showPayment'])->name('payment.show');
        // Route untuk memproses pembayaran
        Route::post('/payment/{booking}', [AdminController::class, 'processPayment'])->name('payment.process');
        // Route untuk menampilkan halaman edit pembayaran
        Route::get('/payment/edit/{id}', [AdminController::class, 'editPayment'])->name('payment.edit');
        // Route untuk memproses pembaruan pembayaran
        Route::post('/payment/update/{id}', [AdminController::class, 'updatePayment'])->name('payment.update');
        // Booking Coach Routes
        Route::get('coach-bookings/create', [AdminController::class, 'createCoachBooking'])->name('bookings.createCoach');
        Route::post('coach-bookings', [AdminController::class, 'storeCoachBooking'])->name('bookings.storeCoach');
        Route::get('coach-bookings/{id}/edit', [AdminController::class, 'editCoachBooking'])->name('bookings.editCoach');
        Route::put('coach-bookings/{id}', [AdminController::class, 'updateCoachBooking'])->name('bookings.updateCoach');
        Route::delete('coach-bookings/{id}', [AdminController::class, 'deleteCoachBooking'])->name('bookings.destroyCoach');

        //Rute Manage Attendance coach
        Route::get('/attendance', [AdminController::class, 'manageAttendance'])->name('attendance');
        Route::get('attendances/create', [AdminController::class, 'createAttendanceCoaches'])->name('attendances.create');
        Route::post('attendances/store', [AdminController::class, 'storeAttendanceCoaches'])->name('attendances.store');
        Route::get('attendances/{id}/edit', [AdminController::class, 'editAttendanceCoaches'])->name('attendances.edit');
        Route::put('attendances/{id}', [AdminController::class, 'updateAttendanceCoaches'])->name('attendances.update');
        Route::delete('attendances/{id}', [AdminController::class, 'destroyAttendanceCoaches'])->name('attendances.delete');
        // Rute Attendance member
        Route::get('member_attendances/create', [AdminController::class, 'createMemberAttendance'])->name('attendances.createAttmember');
        Route::post('member_attendances', [AdminController::class, 'storeMemberAttendance'])->name('attendances.storeAttmember');
        Route::get('member_attendances/{id}/edit', [AdminController::class, 'editMemberAttendance'])->name('attendances.editAttmember');
        Route::put('member_attendances/{id}', [AdminController::class, 'updateMemberAttendance'])->name('attendances.updateAttmember');
        Route::delete('member_attendances/{id}', [AdminController::class, 'destroyMemberAttendance'])->name('attendances.deleteAttmember');
        // Rute manage category
        Route::get('/category', [AdminController::class, 'manageCategory'])->name('category');
        Route::get('categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
        Route::post('categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::get('categories/{id}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('categories/{id}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        // Rute manage Room
        Route::get('/rooms', [AdminController::class, 'manageRoom'])->name('rooms');
        Route::get('room/create', [AdminController::class, 'createRoom'])->name('room.create');
        Route::post('room', [AdminController::class, 'storeRoom'])->name('room.store');
        Route::get('room/{id}/edit', [AdminController::class, 'editRoom'])->name('room.edit');
        Route::put('room/{id}', [AdminController::class, 'updateRoom'])->name('room.update');
        Route::delete('room/{id}', [AdminController::class, 'destroyRoom'])->name('room.destroy');
        // Route untuk memproses QR code
        Route::post('/attendance/scan', [AdminController::class, 'scanQrCode'])->name('attendance.scan');


        //LOG
        Route::get('classes/logs', [AdminController::class, 'showLogs'])->name('classes.logs');
        Route::get('users/logs', [AdminController::class, 'showUserLogs'])->name('users.logs');
        Route::get('bookings/logs', [AdminController::class, 'showBookingLogs'])->name('bookings.logs');
        Route::get('attendances/logs', [AdminController::class, 'showAttendanceLogs'])->name('attendances.logs');
        Route::get('categories/logs', [AdminController::class, 'showCategoryLogs'])->name('categories.logs');
        Route::get('room/logs', [AdminController::class, 'showRoomLogs'])->name('room.logs');
        //other
        Route::get('/search', [SearchController::class, 'search'])->name('search');
        Route::get('/api/available-dates', [AdminController::class, 'getAvailableDates']);
        Route::get('/api/coach/{coach}/availability', [AdminController::class, 'getAvailabilityByCoach']);
        Route::get('/api/popular-classes', [AdminController::class, 'getPopularClasses']);

    });
});
Route::middleware(['auth', 'role:coach'])->group(function () {
    Route::get('/coach/dashboard', [CoachController::class, 'index'])->name('coach.dashboard');
    Route::get('/api/coach/classes', [AdminController::class, 'getClasses']);
    Route::get('/api/coach/coach-bookings', [AdminController::class, 'getCoachBookings']);
    Route::get('/coach/check-availability', [CoachController::class, 'checkAvailability']);




    Route::prefix('coach')->name('coach.')->group(function () {
        Route::get('/kelas', [CoachController::class, 'coachClasses'])->name('kelas');
        Route::get('/booking', [CoachController::class, 'showCoachBookings'])->name('booking');

        // Route untuk absensi coach
        Route::get('/attendance', [CoachController::class, 'coachAbsen'])->name('attendance');
        Route::post('/attendance', [CoachController::class, 'storeAttendance'])->name('attendance.store');

        // Member Attendance
        Route::get('/memberAttendance', [CoachController::class, 'showMemberAttendance'])->name('memberAttendance');
        Route::post('/memberAttendance', [CoachController::class, 'storeMemberAttendance'])->name('memberAttendance.store');

        // Routes untuk manajemen attendance member
        Route::get('/attendances/create', [CoachController::class, 'createMemberAttendance'])->name('attendances.create');
        Route::post('/attendances/store', [CoachController::class, 'storeMemberAttendance'])->name('attendances.store');
        Route::get('/attendances/edit/{id}', [CoachController::class, 'editMemberAttendance'])->name('attendances.edit');
        Route::put('/attendances/update/{id}', [CoachController::class, 'updateMemberAttendance'])->name('attendances.update');
        Route::delete('/attendances/destroy/{id}', [CoachController::class, 'destroyMemberAttendance'])->name('attendances.destroy');

        // QR Code scan route
        Route::post('/attendances/scan', [CoachController::class, 'scanQrCode'])->name('attendances.scan');
    });
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile-admin', [ProfileController::class, 'editAdmin'])->name('profile.edita');
    Route::get('/profile-coach', [ProfileController::class, 'editCoach'])->name('profile.editc'); // Tambahkan route ini
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



require __DIR__ . '/auth.php';
