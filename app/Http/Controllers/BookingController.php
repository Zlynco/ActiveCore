<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Classes;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        // Ambil semua booking
        $bookings = Booking::with('class', 'user')->get();

        return view('admin.booking', compact('bookings'));
    }

    public function create()
    {
        // Ambil semua kelas yang tersedia
        $classes = Classes::all();

        return view('admin.bookings.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'booking_date' => 'required|date',
        ]);

        // Simpan booking baru
        Booking::create([
            'user_id' => Auth::id(),
            'class_id' => $request->class_id,
            'booking_date' => $request->booking_date,
        ]);

        return redirect()->route('admin.booking')->with('success', 'Booking created successfully.');
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $classes = Classes::all();

        return view('admin.bookings.edit', compact('booking', 'classes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'booking_date' => 'required|date',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update([
            'class_id' => $request->class_id,
            'booking_date' => $request->booking_date,
        ]);

        return redirect()->route('admin.booking')->with('success', 'Booking updated successfully.');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->route('admin.booking')->with('success', 'Booking deleted successfully.');
    }
}