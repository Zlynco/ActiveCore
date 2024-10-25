<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ClassResource;
use App\Http\Resources\CoachBookingResource;
use App\Http\Resources\CoachResource;
use App\Http\Resources\MemberResource;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\CoachBooking;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function apikelas()
    {
        $classes = Classes::all();
        //return response()->json(['data' => $classes]);
        return ClassResource::collection($classes);
    }
    public function apiMember()
    {
        $member = User::where('role', 'member')->get();
        //return response()->json(['data' => $member]);
        return MemberResource::collection($member);
    }
    public function apiCoach()
    {
        $coach = User::where('role', 'coach')->get();
        //return response()->json(['data' => $coach]);
        return CoachResource::collection($coach);
    }
    public function apiBooking()
    {
        $bookings = Booking::all();
        //return response()->json(['data' => $bookings]);
        return BookingResource::collection($bookings);
    }
    public function apiCoachBooking()
    {
        $coachbookings = CoachBooking::all();
        //return response()->json(['data' => $coachbookings]);
        return CoachBookingResource::collection($coachbookings);
    }
    public function apiAttendance()
    {
        $attendance = Attendance::all();
        //return response()->json(['data' => $attendance]);
        return AttendanceResource::collection($attendance);
    }
}
