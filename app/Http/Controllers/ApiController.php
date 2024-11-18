<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ClassResource;
use App\Http\Resources\CoachBookingResource;
use App\Http\Resources\CoachResource;
use App\Http\Resources\MemberResource;
use App\Http\Resources\MeResource;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\CoachBooking;
use App\Models\User;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    //CLASS DATA API
    public function apikelasCoach()
    {
        $coach = Auth::user();
        $classes = $coach->classes;
        //return response()->json(['data' => $classes]);
        return ClassResource::collection($classes);
    }
    public function apikelas()
    {
        $classes = Classes::all();
        //return response()->json(['data' => $classes]);
        return ClassResource::collection($classes);
    }

    public function storeClass(Request $request): JsonResponse
    {
        Log::channel('classes')->info('Proses pembuatan kelas baru dimulai.');

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'coach_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'quota' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'room_id' => 'nullable|exists:rooms,id',
            'recurrence' => 'required|in:once,monthly',
        ]);

        // Cek kapasitas ruangan
        $room = Room::find($request->room_id);
        if ($room && $request->quota > $room->capacity) {
            return response()->json([
                'success' => false,
                'message' => 'Quota tidak boleh lebih dari kapasitas ruangan: ' . $room->capacity
            ], 400);
        }

        // Cek apakah ada kelas lain pada hari dan waktu yang sama di ruangan yang sama
        $existingClassInSameRoom = Classes::where('day_of_week', $request->day_of_week)
            ->where('date', $request->date)
            ->where('room_id', $request->room_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        // Cek apakah ada kelas lain dengan coach yang sama pada waktu yang sama
        $existingClassForSameCoach = Classes::where('date', $request->date)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->where('coach_id', $request->coach_id)
            ->exists();

        $roomName = $room->name ?? 'Ruang tidak ditemukan';
        if ($existingClassInSameRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah ada kelas pada hari dan waktu yang sama di room: ' . $roomName
            ], 400);
        }

        if ($existingClassForSameCoach) {
            return response()->json([
                'success' => false,
                'message' => 'Coach sudah memiliki kelas pada hari dan waktu yang sama.'
            ], 400);
        }

        // Unggah gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/classes', 'public');
        }

        // Jika pilihan untuk membuat jadwal bulanan dipilih
        if ($request->recurrence === 'monthly') {
            $this->createMonthlySchedule($request, $imagePath);
        } else {
            // Simpan kelas baru untuk satu kali jadwal
            $newClass = Classes::create([
                'name' => $request->name,
                'description' => $request->description,
                'day_of_week' => $request->day_of_week,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'price' => $request->price,
                'coach_id' => $request->coach_id,
                'image' => $imagePath ? Storage::url($imagePath) : null,
                'quota' => $request->quota,
                'registered_count' => 0,
                'category_id' => $request->category_id,
                'room_id' => $request->room_id,
                'recurrence' => 'once',
            ]);
        }

        Log::channel('classes')->info('Kelas baru berhasil dibuat.', ['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dibuat.',
            'data' => $newClass ?? []
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
    // Log permulaan proses booking
    Log::channel('booking')->info('Proses penyimpanan booking coach dimulai.');

    // Validasi input dari request
    $validatedData = $request->validate([
        'coach_id' => 'required|exists:users,id',
        'booking_date' => 'required|date',
        'start_booking_time' => 'required|date_format:H:i',
        'end_booking_time' => 'required|date_format:H:i|after:start_booking_time',
    ]);

    $userId = Auth::id();
    $coachId = $validatedData['coach_id'];
    $bookingDate = Carbon::parse($validatedData['booking_date']);

    // Konversi waktu menjadi format H:i:s
    $startBookingTime = Carbon::parse($validatedData['start_booking_time'])->format('H:i:s');
    $endBookingTime = Carbon::parse($validatedData['end_booking_time'])->format('H:i:s');

    // 1. Periksa apakah coach memiliki status 'Excused' pada tanggal booking
    $excusedAttendance = Attendance::where('user_id', $coachId)
        ->whereDate('attendance_date', $bookingDate->format('Y-m-d'))
        ->where('status', 'Excused')
        ->exists();

    if ($excusedAttendance) {
        return response()->json([
            'success' => false,
            'message' => 'Coach is excused on the selected date and cannot be booked.',
        ], 400);
    }

    // 2. Periksa apakah coach sudah memiliki booking pada tanggal dan waktu yang sama
    $hasBooking = CoachBooking::where('coach_id', $coachId)
        ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
        ->where(function ($query) use ($startBookingTime, $endBookingTime) {
            $query->whereBetween('start_booking_time', [$startBookingTime, $endBookingTime])
                ->orWhereBetween('end_booking_time', [$startBookingTime, $endBookingTime])
                ->orWhere(function ($q) use ($startBookingTime, $endBookingTime) {
                    $q->where('start_booking_time', '<=', $startBookingTime)
                        ->where('end_booking_time', '>=', $endBookingTime);
                });
        })
        ->exists();

    if ($hasBooking) {
        return response()->json([
            'success' => false,
            'message' => 'Coach is unavailable for the selected date and time.',
        ], 400);
    }

    // 3. Periksa apakah coach sudah memiliki kelas pada hari dan waktu yang sama
    $daysOfWeek = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $dayOfWeekEnglish = $bookingDate->format('l');
    $dayOfWeek = $daysOfWeek[$dayOfWeekEnglish];

    $hasClass = Classes::where('coach_id', $coachId)
        ->where('day_of_week', $dayOfWeek)
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
        return response()->json([
            'success' => false,
            'message' => 'Coach has a class scheduled during the selected time.',
        ], 400);
    }

    // 4. Buat booking baru jika semua validasi lolos
    $nextCoachBookingId = CoachBooking::max('id') + 1;
    $bookingCode = $this->generateBookingCode('CCH', $nextCoachBookingId);

    $existingBooking = CoachBooking::where('user_id', $userId)
        ->where('coach_id', $coachId)
        ->latest()
        ->first();

    $newSessionCount = $existingBooking ? $existingBooking->session_count + 1 : 1;
    $paymentRequired = $newSessionCount % 4 == 0;

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

    // Log data berhasil disimpan
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

    return response()->json([
        'success' => true,
        'message' => 'Coach booked successfully!',
        'data' => $booking,
    ]);
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
    public function registerStoreAuth(Request $request): JsonResponse
{
    // Validasi input
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|string|in:member,coach', // Validasi untuk role
        'phone_number' => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
    ]);

    // Buat pengguna baru
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role, // Menyimpan role dari request
        'phone_number' => $request->phone_number,
    ]);

    // Event untuk mengirim notifikasi jika diperlukan
    event(new Registered($user));

    // Login pengguna
    Auth::login($user);

    return response()->json([
        'message' => 'Registration successful',
        'user' => new MeResource($user) // Mengembalikan informasi pengguna yang baru terdaftar
    ], 201);
}

    public function loginStoreAuth(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($request->only('name','email', 'password'))) {
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
    //OTHERS
    public function getClasses()
    {
        // Ambil data coach yang sedang login
        $coach = Auth::user();

        // Ambil kelas yang harus diajar oleh coach
        $classes = Classes::where('coach_id', $coach->id)->with('category')->get();

        // Format data untuk API
        $events = $classes->map(function ($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'description' => $class->description,
                'date' => Carbon::parse($class->date)->format('Y-m-d'), // Menggunakan Carbon untuk mengonversi dan format
                'day_of_week' => Carbon::parse($class->date)->format('l'), // Menggunakan Carbon untuk mengonversi dan mendapatkan hari
                'category_id' => $class->category_id ? $class->category->name : 'No Category', // Mengambil nama kategori
                'start_time' => Carbon::parse($class->date . ' ' . $class->start_time)->format('Y-m-d\TH:i:s'), // Gabungkan tanggal dan waktu mulai
                'end_time' => Carbon::parse($class->date . ' ' . $class->end_time)->format('Y-m-d\TH:i:s'), // Gabungkan tanggal dan waktu selesai
                'price' => $class->price,
                'room' => $class->room->name ?? 'No Room Assigned',
                'registered_count' => $class->registered_count,
            ];
        });

        return response()->json($events);
    }

    public function getCoachBookings(Request $request)
    {
        // Ambil ID coach dari yang sedang login
        $coachId = auth()->user()->id;

        $bookings = CoachBooking::with(['coach', 'member']) // Pastikan untuk memuat relasi dengan member
            ->where('coach_id', $coachId) // Filter berdasarkan ID coach yang sedang login
            ->where('booking_date', '>=', now()) // Mengambil booking yang akan datang
            ->get();

        // Format data untuk FullCalendar
        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'title' => 'Booked with ' . $booking->member->name, // Tampilkan nama member
                'start' => $booking->booking_date . 'T' . $booking->start_booking_time,
                'end' => $booking->booking_date . 'T' . $booking->end_booking_time,
                'extendedProps' => [
                    'coach' => $booking->coach->name,
                    'member' => $booking->member->name, // Menyimpan nama member
                    'session_count' => $booking->session_count,
                ],
            ];
        });

        return response()->json($formattedBookings);
    }
    public function getPopularCategory()
    {
        $currentMonth = Carbon::now()->month;

        $popularClasses = DB::table('bookings')
            ->join('classes', 'bookings.class_id', '=', 'classes.id')
            ->join('categories', 'classes.category_id', '=', 'categories.id')
            ->whereMonth('bookings.booking_date', $currentMonth)
            ->select('categories.name', DB::raw('COUNT(bookings.id) as total_bookings'))
            ->groupBy('categories.name')
            ->get();

        return response()->json($popularClasses);
    }
    public function getAvailableTimes(Request $request)
    {
        // Validasi input
        $request->validate([
            'coach_id' => 'required|exists:users,id', // Pastikan coach_id ada di tabel users
            'booking_date' => 'required|date', // Pastikan booking_date adalah tanggal yang valid
        ]);

        $coachId = $request->input('coach_id');
        $bookingDate = Carbon::parse($request->input('booking_date'));

        // 1. Periksa apakah coach memiliki status 'Excused' pada tanggal booking
        $excusedAttendance = Attendance::where('user_id', $coachId)
            ->whereDate('attendance_date', $bookingDate->format('Y-m-d'))
            ->where('status', 'Excused')
            ->exists();

        if ($excusedAttendance) {
            // Jika coach berstatus 'Excused', tidak ada jam yang tersedia
            return response()->json([]);
        }

        // Ambil jam yang sudah dibooking pada tanggal tersebut
        $bookedTimes = CoachBooking::where('coach_id', $coachId)
            ->whereDate('booking_date', $bookingDate)
            ->get(['start_booking_time', 'end_booking_time']);

        // Ambil jam yang sudah dikeluarkan dari kelas
        $classTimes = Classes::where('coach_id', $coachId)
            ->where('day_of_week', $bookingDate->format('l'))
            ->get(['start_time', 'end_time']);

        // Logika untuk menentukan jam yang tidak tersedia
        $unavailableTimes = [];
        foreach ($bookedTimes as $booking) {
            $unavailableTimes[] = [
                'start' => $booking->start_booking_time,
                'end' => $booking->end_booking_time,
            ];
        }
        foreach ($classTimes as $class) {
            $unavailableTimes[] = [
                'start' => $class->start_time,
                'end' => $class->end_time,
            ];
        }

        $availableTimes = [];
        $startHour = 8; // 08:00
        $endHour = 19; // 19:30

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                // Awal dan akhir interval saat ini
                $intervalStart = sprintf('%02d:%02d', $hour, $minute);
                $intervalEnd = sprintf('%02d:%02d', $hour, $minute + 30);

                // Konversi waktu ke format menit untuk memudahkan perhitungan
                $intervalStartMinutes = $hour * 60 + $minute;
                $intervalEndMinutes = $hour * 60 + $minute + 30;

                $isUnavailable = false;

                // Cek overlap dengan waktu yang tidak tersedia
                foreach ($unavailableTimes as $unavailable) {
                    // Konversi waktu yang tidak tersedia ke format menit
                    $unavailableStartMinutes = (int)substr($unavailable['start'], 0, 2) * 60 + (int)substr($unavailable['start'], 3, 2);
                    $unavailableEndMinutes = (int)substr($unavailable['end'], 0, 2) * 60 + (int)substr($unavailable['end'], 3, 2);

                    // Periksa apakah interval ini overlap dengan interval yang tidak tersedia
                    if (
                        ($intervalStartMinutes < $unavailableEndMinutes && $intervalEndMinutes > $unavailableStartMinutes)
                    ) {
                        $isUnavailable = true;
                        break;
                    }
                }

                // Jika tidak ada overlap, tambahkan ke daftar waktu yang tersedia
                if (!$isUnavailable) {
                    $availableTimes[] = $intervalStart;
                }
            }
        }

        // Kembalikan waktu yang tersedia dalam format JSON
        return response()->json($availableTimes);
    }

}
