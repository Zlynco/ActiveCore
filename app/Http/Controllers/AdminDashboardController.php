<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\CoachBooking;
use App\Models\MemberAttendance;
use App\Models\PendingRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Mengambil data dari database
    // Mengambil data dari database
    $totalUsers = User::count();
    $totalClasses = Classes::whereMonth('date', now()->month)
    ->whereYear('date', now()->year)
    ->count();
    $totalBookings = Booking::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count() +
    CoachBooking::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count();

    $totalAttendances = Attendance::count() + MemberAttendance::count();

    // Ambil data booking bulan ini
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    // Menghitung pendapatan bulanan
    $totalEarnings = DB::table('bookings')
        ->whereMonth('booking_date', $currentMonth)
        ->whereYear('booking_date', $currentYear)
        ->where('paid', '1') // Hanya booking yang sudah dibayar
        ->sum('amount'); // Pastikan 'amount' sesuai dengan nama kolom di tabel bookings

    // Ambil data booking per kategori kelas
    $popularClasses = DB::table('bookings')
        ->join('classes', 'bookings.class_id', '=', 'classes.id')
        ->join('categories', 'classes.category_id', '=', 'categories.id')
        ->whereMonth('bookings.booking_date', $currentMonth)
        ->where('bookings.paid', '1') // Menambahkan kondisi untuk booking yang sudah dibayar
        ->select('categories.name', DB::raw('count(bookings.id) as total_bookings'))
        ->groupBy('categories.name')
        ->get();

    // Mengambil data pendapatan bulanan untuk grafik
    $earningsData = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyEarnings = DB::table('bookings')
            ->whereMonth('booking_date', $i)
            ->whereYear('booking_date', $currentYear)
            ->where('paid', '1')
            ->sum('amount');
        $earningsData[] = $monthlyEarnings;
    }

        return view('admin.dashboard', compact('totalUsers', 'totalClasses', 'totalBookings', 'totalAttendances', 'totalEarnings', 'popularClasses', 'earningsData', 'monthlyEarnings'));
    }
}
