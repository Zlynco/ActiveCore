<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ClassResource;
use App\Http\Resources\CoachBookingResource;
use App\Http\Resources\CoachResource;
use App\Http\Resources\MemberResource;
use App\Http\Resources\MeResource;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\CoachBooking;
use App\Models\User;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    //CLASS DATA API
    public function apikelas()
    {
        $classes = Classes::all();
        //return response()->json(['data' => $classes]);
        return ClassResource::collection($classes);
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'coach_id' => 'required|exists:users,id',
            'quota' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'room_id' => 'nullable|exists:rooms,id',
            'recurrence' => 'required|in:once,monthly',
        ]);
        $class = Classes::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'day_of_week' => $request->input('day_of_week'),
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'price' => $request->input('price'),
            'coach_id' => $request->input('coach_id'),
            'quota' => $request->input('quota'),
            'category_id' => $request->input('category_id'),
            'room_id' => $request->input('room_id'),
            'recurrence' => $request->input('recurrence'),
        ]);

        return response()->json([
            'message' => 'Class created successfully',
            'class' => $class
        ], 201);
    }
    public function destroyClass($id)
    {

        Log::channel('classes')->info('Menghapus kelas.', ['class_id' => $id]);

        $class = Classes::findOrFail($id);

        // Hapus gambar jika ada
        if ($class->image) {
            Storage::delete($class->image);
            Log::channel('classes')->info('Gambar kelas dihapus.', ['class_id' => $id, 'image_path' => $class->image]);
        }

        $class->delete();

        Log::channel('classes')->info('Kelas berhasil dihapus.', ['class_id' => $id]);

        return response()->json([
            'message' => 'Class deleted successfully.',
            'class_id' => $id
        ], 200);
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
    //BOOKING CLASS API
    public function apiBooking()
    {
        $bookings = Booking::all();
        //return response()->json(['data' => $bookings]);
        return BookingResource::collection($bookings);
    }
    public function storeBooking(Request $request)
    {
        Log::channel('booking')->info('Proses penyimpanan booking dimulai.', [
            'user_id' => Auth::id(),
            'class_id' => $request->class_id,
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $class = Classes::findOrFail($request->class_id);

        $bookingDate = $class->date;

        $currentBookings = Booking::where('class_id', $class->id)
            ->whereDate('booking_date', $bookingDate)
            ->where('paid', true)
            ->count();

        $availableQuota = $class->quota - $currentBookings;

        if ($availableQuota <= 0) {
            Log::channel('booking')->warning('Kuota kelas penuh.', [
                'class_id' => $class->id,
                'registered_count' => $currentBookings,
                'quota' => $class->quota
            ]);

            return response()->json(['message' => 'Kuota kelas penuh.'], 400);
        }
        $nextBookingId = Booking::max('id') + 1;
        $bookingCode = $this->generateBookingCode('CLS', $nextBookingId);

        $booking = Booking::create([
            'class_id' => $request->class_id,
            'user_id' => Auth::id(),
            'booking_date' => $bookingDate,
            'booking_code' => $bookingCode,
            'amount' => $class->price,
            'paid' => false,
        ]);

        $this->generateQRCode($bookingCode);

        Log::channel('booking')->info('Booking baru berhasil disimpan.', [
            'booking_code' => $bookingCode,
            'class_id' => $class->id,
            'user_id' => Auth::id(),
            'booking_date' => $bookingDate,
        ]);

        return response()->json([
            'message' => 'Booking added successfully.',
            'booking' => $booking,
        ], 201);
    }

    //BOOKING COACH API
    public function apiCoachBooking()
    {
        $coachbookings = CoachBooking::all();
        //return response()->json(['data' => $coachbookings]);
        return CoachBookingResource::collection($coachbookings);
    }
    public function storeCoachBooking(Request $request)
    {
        Log::channel('booking')->info('Proses penyimpanan booking coach dimulai.');

        // Validasi input dari request
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'booking_date' => 'required|date',
            'start_booking_time' => 'required|date_format:H:i',
            'end_booking_time' => 'required|date_format:H:i',
        ]);

        $userId = Auth::id();
        $coachId = $request->input('coach_id');
        $bookingDate = Carbon::parse($request->input('booking_date'));

        // Mengonversi waktu mulai dan selesai booking menjadi format H:i:s
        $startBookingTime = Carbon::parse($request->input('start_booking_time'))->format('H:i:s');
        $endBookingTime = Carbon::parse($request->input('end_booking_time'))->format('H:i:s');

        // 1. Periksa apakah coach sudah memiliki booking pada tanggal dan waktu yang sama
        $hasBooking = CoachBooking::where('coach_id', $coachId)
            ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
            ->where(function ($query) use ($startBookingTime, $endBookingTime) {
                $query->whereBetween('start_booking_time', [$startBookingTime, $endBookingTime])
                    ->orWhereBetween('end_booking_time', [$startBookingTime, $endBookingTime])
                    ->orWhere(function ($q2) use ($startBookingTime, $endBookingTime) {
                        $q2->where('start_booking_time', '<=', $startBookingTime)
                            ->where('end_booking_time', '>=', $endBookingTime);
                    });
            })
            ->exists();

        if ($hasBooking) {
            return response()->json(['error' => 'Coach is already booked for the selected date and time.'], 400);
        }

        // 2. Ambil nama hari dari tanggal booking yang dipilih
        $daysOfWeek = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $dayOfWeekEnglish = $bookingDate->format('l'); // Ambil nama hari dalam bahasa Inggris
        $dayOfWeek = $daysOfWeek[$dayOfWeekEnglish]; // Ubah ke bahasa Indonesia

        // 3. Periksa apakah coach sudah memiliki kelas pada hari dan waktu yang sama
        $hasClass = Classes::where('coach_id', $coachId)
            ->where('day_of_week', $dayOfWeek) // Cocokkan hari
            ->where(function ($query) use ($startBookingTime, $endBookingTime) {
                $query->whereBetween('start_time', [$startBookingTime, $endBookingTime])
                    ->orWhereBetween('end_time', [$startBookingTime, $endBookingTime])
                    ->orWhere(function ($q) use ($startBookingTime, $endBookingTime) {
                        $q->where('start_time', '<=', $startBookingTime)
                            ->where('end_time', '>=', $endBookingTime);
                    });
            })
            ->exists();

        if ($hasClass) {
            return response()->json(['error' => 'Coach has a class scheduled during the selected time.'], 400);
        }

        // Buat booking baru jika semua validasi lolos
        $nextCoachBookingId = CoachBooking::max('id') + 1;
        $bookingCode = $this->generateBookingCode('CCH', $nextCoachBookingId);

        $existingBooking = CoachBooking::where('user_id', $userId)
            ->where('coach_id', $coachId)
            ->latest()
            ->first();

        $newSessionCount = $existingBooking ? $existingBooking->session_count + 1 : 1;
        $paymentRequired = $newSessionCount % 4 == 0;

        // Buat booking baru
        $booking = CoachBooking::create([
            'coach_id' => $coachId,
            'user_id' => $userId,
            'session_count' => $newSessionCount,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'start_booking_time' => $startBookingTime,
            'end_booking_time' => $endBookingTime,
            'booking_code' => $bookingCode,
            'payment_required' => $paymentRequired,
        ]);
        $coachName = User::find($coachId)->name;

        Log::channel('booking')->info('Booking coach berhasil dibuat.', [
            'user_id' => $userId,
            'coach_id' => $coachId,
            'session_count' => $newSessionCount,
            'payment_required' => $paymentRequired,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'start_booking_time' => $startBookingTime,
            'end_booking_time' => $endBookingTime,
            'booking_code' => $bookingCode,
        ]);

        return response()->json(['success' => 'Coach booked successfully!', 'booking' => $booking, 'coach_name' => $coachName], 201);
    }

    //ATTENDANCE API
    public function apiAttendance()
    {
        $attendance = Attendance::all();
        //return response()->json(['data' => $attendance]);
        return AttendanceResource::collection($attendance);
    }
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',  // class_id bisa null atau ID yang valid
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:Present,Sick,Excused,Absent',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'absence_reason' => 'nullable|string',
            'unique_code' => 'nullable|string',
        ]);

        $uniqueCode = 'ATT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $attendanceData = [
            'user_id' => $request->user_id,
            'class_id' => $request->class_id ?: null,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'absence_reason' => $request->absence_reason,
            'unique_code' => $uniqueCode,
        ];

        // Simpan data absensi ke database
        $attendance = Attendance::create($attendanceData);

        // Log absensi
        Log::channel('attendance')->info('Membuat catatan absensi baru:', $attendanceData);

        // Kembalikan respons JSON
        return response()->json([
            'success' => true,
            'message' => 'Attendance created successfully.',
            'attendance' => $attendance, // Menyertakan data absensi yang baru dibuat
        ], 201);
    }


    //function authentication

    public function storeAuth(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = User::find(Auth::id());

            if ($user->role === 'coach') {
                if ($user->status === 'pending') {
                    Auth::logout();
                    Log::channel('token')->warning('Coach registration pending for user: ' . $user->email);
                    return response()->json([
                        'message' => 'Your coach registration is pending approval. Please wait until an admin approves your registration.'
                    ], 403);
                } elseif ($user->status === 'rejected') {
                    Auth::logout();
                    Log::channel('token')->warning('Coach registration rejected for user: ' . $user->email);
                    return response()->json([
                        'message' => 'Your coach registration has been rejected. Please contact support for more information.'
                    ], 403);
                }

                $token = $user->createToken('authTokenCoach')->plainTextToken;
                Log::channel('token')->info('Generated Token for Coach: ' . $token . ' for user: ' . $user->email);
                return response()->json(['token' => $token, 'role' => 'coach'], 200);
            }

            if ($user->role === 'admin') {
                $token = $user->createToken('authTokenAdmin')->plainTextToken;
                Log::channel('token')->info('Generated Token for Admin: ' . $token . ' for user: ' . $user->email);
                return response()->json(['token' => $token, 'role' => 'admin'], 200);
            }

            $token = $user->createToken('authTokenMember')->plainTextToken;
            Log::channel('token')->info('Generated Token for Member: ' . $token . ' for user: ' . $user->email); // Log token untuk member
            return response()->json(['token' => $token, 'role' => 'member'], 200);
        }

        // Jika autentikasi gagal
        Log::channel('token')->error('Login failed for email: ' . $request->email); // Log kesalahan login
        return response()->json(['error' => 'The provided credentials do not match our records.'], 401);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroyAuth(Request $request): JsonResponse
    {
        // Ambil pengguna yang sedang login
        $user = User::find(Auth::id());

        // Hapus semua token yang terkait dengan pengguna ini
        $user->tokens()->delete();

        // Logout dari sesi
        Auth::logout();

        Log::channel('token')->info('User logged out: ' . $user->email); // Log logout

        return response()->json(['message' => 'Successfully logged out.'], 200);
    }
    public function me()
    {
        return new MeResource(Auth::user());
    }
    public function updateUser(Request $request)
    {
        // Validasi data yang akan diperbarui
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:15',
        ]);

        // Dapatkan pengguna yang sedang terautentikasi
        $user = User::find(Auth::id());

        // Perbarui data pengguna
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        if ($request->filled('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        // Simpan perubahan ke database
        $user->save();

        return response()->json([
            'message' => 'User data updated successfully',
            'user' => $user,
        ]);
    }

    //ANOTHER
    public function generateBookingCode($prefix, $nextId)
    {
        $code = $prefix . '-' . now()->format('Ymd') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        // Periksa apakah kode sudah ada di database (untuk booking dan coach bookings)
        while (Booking::where('booking_code', $code)->exists() || CoachBooking::where('booking_code', $code)->exists()) {
            $nextId++;
            $code = $prefix . '-' . now()->format('Ymd') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }

        return $code;
    }
    private function generateQRCode($bookingCode)
    {
        $qrCodePath = public_path('qrcodes/QR-' . $bookingCode . '.png');

        // Pastikan direktori ada
        if (!File::exists(public_path('qrcodes'))) {
            File::makeDirectory(public_path('qrcodes'), 0755, true);
        }

        // Generate QR code dan simpan ke file
        QrCode::format('png')->size(300)->generate($bookingCode, $qrCodePath);
    }
}
