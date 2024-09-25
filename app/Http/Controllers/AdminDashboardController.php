<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\CoachBooking;
use App\Models\MemberAttendance;
use App\Models\PendingRequest;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Mengambil data dari database
        $totalUsers = User::count();
        $totalClasses = Classes::count(); // Pastikan nama model sesuai
        $totalBookings = Booking::count() + CoachBooking::count();
        $totalAttendances = Attendance::count() + MemberAttendance::count();
    
        // Mengirim data ke view
        return view('admin.dashboard', compact('totalUsers', 'totalClasses', 'totalBookings', 'totalAttendances'));
    }
    
}
