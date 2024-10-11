<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Classes;
use App\Models\ClassLog;
use App\Models\Coach;
use App\Models\CoachBooking;
use App\Models\MemberAttendance;
use App\Models\PendingRequest;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $usersCount = User::count();
        $kelasCount = Classes::count();
        $bookingsCount = Booking::count();
        $coachBookingsCount = CoachBooking::count();
        $totalBookings = $bookingsCount + $coachBookingsCount;
        $pendingRequestsCount = PendingRequest::count();

        return view('admin.dashboard', compact('usersCount', 'kelasCount', 'bookingsCount', 'pendingRequestsCount'));
    }

    // manage user
    public function manageUsers(Request $request)
    {
        $search = $request->input('search');

        Log::channel('userlog')->info('Mengambil data member dan coach dengan pencarian.', ['search' => $search]);

        // Buat query untuk members
        $membersQuery = User::where('role', 'member');
        if ($search) {
            $membersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $members = $membersQuery->get();

        // Buat query untuk coaches
        $coachesQuery = User::where('role', 'coach');
        if ($search) {
            $coachesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $coaches = $coachesQuery->get();

        Log::channel('userlog')->info('Data member dan coach berhasil diambil.', ['members_count' => $members->count(), 'coaches_count' => $coaches->count()]);

        return view('admin.user', compact('members', 'coaches'));
    }



    public function editUser($id)
    {
        Log::channel('userlog')->info('Mengambil data user untuk di-edit.', ['user_id' => $id]);
        $user = User::findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        Log::channel('userlog')->info('Proses update user dimulai.', ['user_id' => $id]);
        $user = User::findOrFail($id);
        $user->update($request->all());

        Log::channel('userlog')->info('User berhasil diupdate.', ['user_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'User updated successfully');
    }

    public function deleteUser($id)
    {
        Log::channel('userlog')->info('Menghapus user.', ['user_id' => $id]);
        $user = User::findOrFail($id);
        $user->delete();

        Log::channel('userlog')->info('User berhasil dihapus.', ['user_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'User deleted successfully');
    }

    // Controller Method untuk Edit Coach
    public function editCoach($id)
    {
        Log::channel('userlog')->info('Mengambil data coach untuk di-edit.', ['coach_id' => $id]);

        // Cari coach berdasarkan ID, gagal jika tidak ditemukan
        $coach = User::findOrFail($id);

        // Ambil semua kategori untuk digunakan dalam form
        $categories = Category::all();

        return view('admin.edit-coach', compact('coach', 'categories'));
    }

    public function updateCoach(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|numeric', // Validasi untuk phone_number
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);
    
        Log::channel('userlog')->info('Proses update coach dimulai.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
        $coach->update($request->only(['name', 'email', 'phone_number'])); // Tambahkan phone_number
    
        // Sinkronisasi kategori
        $coach->categories()->sync($request->categories);
    
        Log::channel('userlog')->info('Coach berhasil diupdate.', ['coach_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'Coach updated successfully');
    }
    

    // Controller Method untuk Delete Coach
    public function deleteCoach($id)
    {
        Log::channel('userlog')->info('Menghapus coach.', ['coach_id' => $id]);

        // Cari coach berdasarkan ID, gagal jika tidak ditemukan
        $coach = User::findOrFail($id);

        // Hapus coach
        $coach->delete();

        Log::channel('userlog')->info('Coach berhasil dihapus.', ['coach_id' => $id]);

        return redirect()->route('admin.user')->with('success', 'Coach deleted successfully');
    }

    public function approveCoach($id)
    {
        Log::info('Menyetujui coach.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
        $coach->status = 'approved';
        $coach->save();

        Log::channel('userlog')->info('Coach berhasil disetujui.', ['coach_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'Coach approved successfully.');
    }

    public function rejectCoach($id)
    {
        Log::channel('userlog')->info('Menolak coach.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
        $coach->status = 'rejected';
        $coach->save();

        Log::channel('userlog')->info('Coach berhasil ditolak.', ['coach_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'Coach rejected successfully.');
    }

    //manage kelas
    public function manageClasses(Request $request)
    {
        $search = $request->input('search');
        $selectedCategory = $request->input('category');
        $selectedDay = $request->input('day');
        $selectedMonth = $request->input('month'); // Ambil bulan yang dipilih

        Log::channel('classes')->info('Mengambil data kelas dengan pencarian.', ['search' => $search]);

        // Ambil semua kategori dari database
        $categories = Category::all();

        // Daftar hari dalam seminggu
        $daysOfWeek = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        // Buat query untuk kelas
        $classesQuery = Classes::with(['coach', 'category', 'room'])->orderBy('date', 'desc');

        // Tambahkan filter pencarian jika ada
        if ($search) {
            $classesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('day_of_week', 'like', "%{$search}%")
                    ->orWhere('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%");
            });
        }

        // Tambahkan filter kategori jika ada
        if ($selectedCategory) {
            $classesQuery->where('category_id', $selectedCategory);
        }

        // Tambahkan filter hari jika ada
        if ($selectedDay) {
            $classesQuery->where('day_of_week', $selectedDay);
        }

        // Tambahkan filter bulan jika ada
        //if ($selectedMonth) {$classesQuery->whereMonth('date', $selectedMonth);}

        // Filter berdasarkan tanggal tunggal
        if ($request->filled('date')) {
            $classesQuery->whereDate('date', $request->date); // Menggunakan whereDate untuk filter tanggal
        }

        // Tambahkan paginasi
        $classes = $classesQuery->paginate(10); // 10 kelas per halaman
        Log::channel('classes')->info('Data kelas berhasil diambil.', ['classes_count' => $classes->total()]);

        return view('admin.kelas', compact('classes', 'search', 'categories', 'daysOfWeek', 'selectedCategory', 'selectedDay', 'selectedMonth')); // Kirim variabel ke view
    }

    public function editClass($id)
    {
        Log::channel('classes')->info('Menyiapkan halaman untuk mengedit kelas.', ['class_id' => $id]);

        $class = Classes::findOrFail($id); // Temukan kelas berdasarkan ID
        $categories = Category::all(); // Ambil semua kategori
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get(); // Ambil pelatih yang disetujui
        $rooms = Room::all(); // Ambil semua ruangan

        Log::channel('classes')->info('Data untuk pengeditan kelas berhasil diambil.', ['class_id' => $id]);

        return view('admin.classes.edit', compact('class', 'categories', 'coaches', 'rooms'));
    }

    public function updateClass(Request $request, $id)
    {
        Log::channel('classes')->info('Proses update kelas dimulai.', ['class_id' => $id]);

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'day_of_week' => 'required|string',
            'date' => 'required|date', // Validasi tanggal kelas
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'coach_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'quota' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'room_id' => 'nullable|exists:rooms,id', // Validasi untuk ruangan
        ]);
        // Cek apakah kuota melebihi kapasitas ruangan
        $room = Room::find($request->room_id);
        if ($room && $request->quota > $room->capacity) {
            return redirect()->back()->withErrors(['error' => 'Kuota kelas melebihi kapasitas ruangan yang dipilih. Kapasitas maksimum: ' . $room->capacity])->withInput();
        }

        // Ambil data kelas
        $class = Classes::findOrFail($id);
        $originalData = $class->toArray(); // Simpan data asli untuk log

        // Cek apakah ada kelas lain pada hari dan waktu yang sama (kecuali kelas itu sendiri)
        $existingClass = Classes::where('coach_id', $request->coach_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('date', $request->date) // Tambahkan pengecekan tanggal
            ->where('id', '!=', $id) // Kecualikan kelas yang sedang diperbarui
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        if ($existingClass) {
            Log::channel('classes')->warning('Kelas tidak bisa diperbarui. Pelatih sudah memiliki kelas pada hari dan waktu yang sama.', [
                'coach_id' => $request->coach_id,
                'day_of_week' => $request->day_of_week,
                'date' => $request->date, // Tambahkan logging untuk tanggal
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
            return redirect()->back()->withErrors(['error' => 'Coach already has a class at this time.']);
        }

        // Unggah gambar jika ada
        $imagePath = $class->image; // Simpan path gambar yang ada
        if ($request->hasFile('image')) {
            Log::channel('classes')->info('Mengunggah gambar baru untuk kelas.', ['class_id' => $id]);
            if ($class->image) {
                Storage::delete($class->image); // Hapus gambar yang lama
            }
            $imagePath = $request->file('image')->store('public/images'); // Simpan gambar baru
        }

        // Update kelas
        $class->update([
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'date' => $request->date, // Update tanggal kelas
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
            'coach_id' => $request->coach_id,
            'image' => $imagePath,
            'quota' => $request->quota,
            'category_id' => $request->category_id,
            'room_id' => $request->room_id, // Menambahkan kolom ruangan
        ]);

        Log::channel('classes')->info('Kelas berhasil diperbarui.', [
            'class_id' => $class->id,
            'original_data' => $originalData,
            'updated_data' => $class->toArray()
        ]);

        return redirect()->route('admin.kelas')->with('success', 'Class updated successfully.');
    }

    public function deleteClass($id)
    {
        Log::channel('classes')->info('Menghapus kelas.', ['class_id' => $id]);

        // Cari kelas berdasarkan ID
        $class = Classes::findOrFail($id);

        // Hapus gambar jika ada
        if ($class->image) {
            Storage::delete($class->image); // Hapus gambar dari storage
            Log::channel('classes')->info('Gambar kelas dihapus.', ['class_id' => $id, 'image_path' => $class->image]);
        }

        // Hapus kelas dari database
        $class->delete();

        // Logging setelah kelas berhasil dihapus
        Log::channel('classes')->info('Kelas berhasil dihapus.', ['class_id' => $id]);

        // Redirect ke halaman kelas dengan pesan sukses
        return redirect()->route('admin.kelas')->with('success', 'Class deleted successfully.');
    }

    public function createClass()
    {
        Log::channel('classes')->info('Menyiapkan halaman untuk membuat kelas baru.');

        $categories = Category::all(); // Ambil semua kategori
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get(); // Ambil semua pelatih yang disetujui
        $rooms = Room::all(); // Ambil semua ruangan

        Log::channel('classes')->info('Data untuk pembuatan kelas baru berhasil diambil.', [
            'total_categories' => $categories->count(),
            'total_coaches' => $coaches->count(),
            'total_rooms' => $rooms->count()
        ]);

        return view('admin.classes.create', compact('categories', 'coaches', 'rooms'));
    }


    public function storeClass(Request $request)
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
            return redirect()->back()->withErrors(['Quota tidak boleh lebih dari kapasitas ruangan: ' . $room->capacity])->withInput();
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
            return redirect()->back()->withErrors(['Sudah ada kelas pada hari dan waktu yang sama di room: ' . $roomName])->withInput();
        }

        if ($existingClassForSameCoach) {
            return redirect()->back()->withErrors(['Coach sudah memiliki kelas pada hari dan waktu yang sama.'])->withInput();
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
            Classes::create([
                'name' => $request->name,
                'description' => $request->description,
                'day_of_week' => $request->day_of_week,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'price' => $request->price,
                'coach_id' => $request->coach_id,
                'image' => $imagePath,
                'quota' => $request->quota,
                'registered_count' => 0,
                'category_id' => $request->category_id,
                'room_id' => $request->room_id,
                'recurrence' => 'once',
            ]);
        }

        Log::channel('classes')->info('Kelas baru berhasil dibuat.', ['name' => $request->name]);

        return redirect()->route('admin.kelas')->with('success', 'Kelas berhasil dibuat.');
    }


    protected function createMonthlySchedule(Request $request, $imagePath)
    {
        // Set locale ke bahasa Indonesia
        Carbon::setLocale('id');

        $startDate = Carbon::parse($request->date);
        $endDate = $startDate->copy()->endOfMonth();

        while ($startDate->lessThanOrEqualTo($endDate)) {
            // Menggunakan translatedFormat untuk mendapatkan nama hari dalam bahasa Indonesia
            $dayOfWeek = $startDate->translatedFormat('l'); // Nama hari dalam bahasa Indonesia

            Classes::create([
                'name' => $request->name,
                'description' => $request->description,
                'day_of_week' => $dayOfWeek,
                'date' => $startDate->format('Y-m-d'),
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'price' => $request->price,
                'coach_id' => $request->coach_id,
                'image' => $imagePath,
                'quota' => $request->quota,
                'registered_count' => 0, // Awal jumlah peserta terdaftar
                'category_id' => $request->category_id,
                'room_id' => $request->room_id,
                'recurrence' => 'monthly',
            ]);

            $startDate->addWeek(); // Tambahkan satu minggu
        }

        Log::channel('classes')->info('Kelas bulanan berhasil dibuat.', ['name' => $request->name]);
    }



    public function getCoachClasses()
    {
        // Ambil kelas yang dimiliki coach yang sedang login
        $classes = Classes::where('coach_id', auth()->id())->get();

        $events = [];

        foreach ($classes as $class) {
            $startDate = $this->getNextClassDate($class->day_of_week); // Dapatkan tanggal kelas berikutnya
            $startDateTime = $startDate->format('Y-m-d') . 'T' . $class->start_time; // Format waktu mulai
            $endDateTime = $startDate->format('Y-m-d') . 'T' . $class->end_time; // Format waktu selesai

            $events[] = [
                'title' => $class->name,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'description' => $class->description,
                'price' => $class->price,
            ];
        }

        return response()->json($events); // Mengembalikan data dalam format JSON
    }

    public function getNextClassDate($dayOfWeek)
    {
        // Hari ini
        $today = now();

        // Konversi nama hari ke angka (0 untuk Minggu, 1 untuk Senin, dst.)
        $daysOfWeek = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        // Hitung selisih hari sampai hari kelas berikutnya
        $targetDayOfWeek = $daysOfWeek[$dayOfWeek];
        $diffInDays = ($targetDayOfWeek + 7 - $today->dayOfWeek) % 7;

        // Jika hari kelas adalah hari ini, maka tambahkan 7 hari untuk mendapatkan kelas berikutnya
        if ($diffInDays == 0) {
            $diffInDays = 7;
        }

        return $today->addDays($diffInDays);
    }

    public function scanQRCodeBook(Request $request, $id)
    {
        $booking = Booking::find($id);

        // Cek apakah booking ada
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        // Cek jika booking sudah di-scan
        if ($booking->scanned) {
            return response()->json(['message' => 'QR Code already scanned.'], 400);
        }

        // Update status pemindaian
        $booking->scanned = true;
        $booking->scanned_at = now(); // Jika perlu, tambahkan kolom ini di migrasi juga
        $booking->save();

        return response()->json(['message' => 'QR Code scanned successfully.'], 200);
    }



    // Function untuk menampilkan halaman class admin
    public function showClasses()
    {
        $classes = Classes::all();

        // Menggunakan fungsi untuk menghitung tanggal kelas berikutnya
        foreach ($classes as $class) {
            $class->nextClassDate = $this->getNextClassDate($class->day_of_week);
        }

        return view('admin.kelas', compact('classes'));
    }
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
    public function showPayment($bookingId)
    {
        Log::info('Menampilkan halaman pembayaran.', ['booking_id' => $bookingId]);

        $booking = Booking::with('class')->findOrFail($bookingId);

        // Pastikan booking belum dibayar
        if ($booking->paid) {
            return redirect()->route('admin.booking')->with('info', 'Booking already paid.');
        }

        return view('admin.payment.show', compact('booking'));
    }

    public function processPayment(Request $request, $bookingId)
    {
        Log::info('Memproses pembayaran.', ['booking_id' => $bookingId]);

        $booking = Booking::findOrFail($bookingId);

        // Validasi data pembayaran
        $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        // Periksa apakah jumlah yang dibayar sesuai dengan jumlah yang harus dibayar
        if ($request->amount != $booking->amount) {
            return redirect()->back()->withErrors('Amount does not match the required amount.');
        }

        // Konfirmasi pembayaran
        $booking->paid = true;
        $booking->save();

        // Update kuota jika perlu
        $class = $booking->class;
        $currentBookings = $class->bookings()->where('paid', true)->count();
        if ($currentBookings > $class->quota) {
            return redirect()->route('admin.booking')->withErrors('Class quota exceeded.');
        }

        Log::info('Pembayaran berhasil diproses.', ['booking_id' => $bookingId]);

        return redirect()->route('admin.booking')->with('success', 'Payment processed successfully.');
    }
    public function showBookings()
    {
        $bookings = Booking::with('member')->get();

        foreach ($bookings as $booking) {
            Log::channel('booking')->info('Booking Member:', [
                'booking_id' => $booking->id,
                'member' => $booking->member->name, // Menampilkan nama member untuk detail lebih jelas
            ]);
        }

        Log::channel('booking')->info('Semua data booking berhasil ditampilkan.');

        return view('admin.booking', compact('bookings'));
    }
    public function manageBooking(Request $request)
    {
        $search = $request->input('search');
        $categories = Category::all();
        Log::channel('booking')->info('Mengambil semua data booking dengan pencarian.', ['search' => $search]);

        // Ambil data booking dengan pencarian
        $bookingsQuery = Booking::with(['class', 'member']);
        $coachBookingsQuery = CoachBooking::with('coach', 'member');

        if ($search) {
            $bookingsQuery->where(function ($q) use ($search) {
                $q->whereHas('class', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('member', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('booking_code', 'like', "%{$search}%");
            });

            $coachBookingsQuery->where(function ($q) use ($search) {
                $q->whereHas('coach', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('member', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('booking_code', 'like', "%{$search}%");
            });
        }

        $bookings = $bookingsQuery->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $coachBookings = $coachBookingsQuery->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Tambahkan kuota terisi per tanggal ke setiap booking
        $bookings = $bookings->map(function ($booking) {
            // Hitung kuota terisi untuk kelas pada tanggal tertentu
            $quotaFilled = $booking->class->bookings()
                ->whereDate('booking_date', $booking->booking_date)
                ->where('paid', true)
                ->count();

            return [
                'class_name' => $booking->class->name,
                'coach_name' => $booking->class->coach->name,
                'member_name' => $booking->member ? $booking->member->name : 'No Member Assigned',
                'day_of_week' => $booking->class->day_of_week,
                'start_time' => $booking->class->start_time,
                'end_time' => $booking->class->end_time,
                'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'amount' => $booking->amount,
                'paid' => $booking->paid ? 'Yes' : 'No',
                'quota_filled' => $quotaFilled,
                'quota' => $booking->class->quota,
                'booking_code' => $booking->booking_code,
                'id' => $booking->id,
            ];
        });

        Log::channel('booking')->info('Data booking berhasil diambil.', [
            'bookings_count' => $bookings->count(),
            'coach_bookings_count' => $coachBookings->count(),
        ]);

        return view('admin.booking', compact('bookings', 'coachBookings', 'categories'));
    }
    public function createBooking(Request $request)
{
    Log::channel('booking')->info('Menyiapkan halaman untuk membuat booking baru.');

    // Ambil semua kategori
    $categories = Category::all();

    // Ambil semua kelas, termasuk gambar dan booking yang terkait
    $classesQuery = Classes::query();

    // Filter kelas yang waktunya belum berlalu
    $now = Carbon::now(); // Mendapatkan waktu sekarang
    $classesQuery->where('date', '>=', $now->toDateString()) // Memastikan tanggal kelas tidak di masa lalu
                  ->where(function($query) use ($now) {
                      $query->where('start_time', '>', $now->toTimeString())
                            ->orWhere('date', '>', $now->toDateString());
                  });

    // Filter berdasarkan tanggal jika ada
    if ($request->has('booking_date') && !empty($request->booking_date)) {
        $classesQuery->whereDate('date', $request->booking_date);
    }

    // Filter kelas berdasarkan kategori jika ada
    if ($request->has('category') && !empty($request->category)) {
        $classesQuery->where('category_id', $request->category);
    }

    // Ambil kelas yang telah difilter
    $classes = $classesQuery->get();

    // Logging data kelas
    Log::channel('booking')->info('Data kelas berhasil dipersiapkan.', [
        'classes_count' => $classes->count(),
        'date_generated' => Carbon::now()->toDateTimeString()
    ]);

    // Kirim data ke view
    return view('admin.bookings.create', compact('classes', 'categories'));
}


    public function storeBooking(Request $request)
    {
        // Mulai logging proses booking
        Log::channel('booking')->info('Proses penyimpanan booking dimulai.', [
            'user_id' => Auth::id(),
            'class_id' => $request->class_id,
        ]);

        // Validasi input hanya untuk class_id karena tanggal sudah ada di kelas
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        // Cari kelas berdasarkan ID yang dipilih
        $class = Classes::findOrFail($request->class_id);

        // Ambil tanggal kelas dari data kelas
        $bookingDate = $class->date;  // Ambil tanggal dari kelas (misal kolom 'date')

        // Hitung jumlah pemesanan yang telah dibayar untuk tanggal yang dipilih
        $currentBookings = Booking::where('class_id', $class->id)
            ->whereDate('booking_date', $bookingDate)
            ->where('paid', true) // Hanya hitung booking yang sudah dibayar
            ->count();

        // Cek apakah kuota yang tersisa cukup untuk pendaftaran baru
        $availableQuota = $class->quota - $currentBookings;

        // Jika kuota tidak cukup, kirim peringatan dan kembalikan ke halaman sebelumnya
        if ($availableQuota <= 0) {
            Log::channel('booking')->warning('Kuota kelas penuh.', [
                'class_id' => $class->id,
                'registered_count' => $currentBookings,
                'quota' => $class->quota
            ]);

            return redirect()->back()->with('quota_full', true);
        }

        // Generate kode booking unik
        $nextBookingId = Booking::max('id') + 1;
        $bookingCode = $this->generateBookingCode('CLS', $nextBookingId);

        // Simpan booking ke dalam database
        $booking = Booking::create([
            'class_id' => $request->class_id,
            'user_id' => Auth::id(),
            'booking_date' => $bookingDate, // Gunakan tanggal dari kelas
            'booking_code' => $bookingCode,
            'amount' => $class->price,
            'paid' => false, // Menandakan bahwa booking belum dibayar
        ]);

        // Generate QR code untuk booking
        $this->generateQRCode($bookingCode);

        // Logging suksesnya proses booking
        Log::channel('booking')->info('Booking baru berhasil disimpan.', [
            'booking_code' => $bookingCode,
            'class_id' => $class->id,
            'user_id' => Auth::id(),
            'booking_date' => $bookingDate,
        ]);

        return redirect()->route('admin.booking')->with('success', 'Booking added successfully. QR Code generated for booking.');
    }


    public function editBooking($id)
    {
        Log::channel('booking')->info('Mengakses halaman edit untuk booking.', ['booking_id' => $id]);

        // Cari booking berdasarkan ID
        $booking = Booking::findOrFail($id);
        $categories = Category::all();

        // Ambil kelas yang tersedia untuk diubah
        $classes = Classes::all();

        Log::channel('booking')->info('Data booking berhasil ditemukan untuk diedit.', [
            'booking_id' => $id,
            'class_id' => $booking->class_id,
            'booking_date' => $booking->booking_date
        ]);

        return view('admin.bookings.edit', compact('booking', 'classes', 'categories'));
    }

    public function updateBooking(Request $request, $id)
    {
        Log::channel('booking')->info('Proses update booking dimulai.', ['booking_id' => $id]);

        // Validasi input
        $request->validate([
            'booking_date' => 'required|date_format:Y-m-d',
            'paid' => 'boolean', // Validasi untuk paid (checkbox)
        ]);

        // Cari booking berdasarkan ID
        $booking = Booking::findOrFail($id);
        $class = Classes::findOrFail($booking->class_id);

        $previousPaidStatus = $booking->paid; // Simpan status sebelumnya
        $booking->booking_date = $request->booking_date;
        $booking->paid = $request->has('paid'); // Jika checkbox checked, maka set paid = true

        // Simpan perubahan
        $booking->save();

        // Perbarui registered_count hanya jika status paid berubah dari false ke true
        if (!$previousPaidStatus && $booking->paid) {
            $class->increment('registered_count');
        } elseif ($previousPaidStatus && !$booking->paid) {
            $class->decrement('registered_count');
        }

        Log::channel('booking')->info('Booking berhasil diupdate.', [
            'booking_id' => $booking->id,
            'updated_booking_date' => $booking->booking_date,
            'paid_status' => $booking->paid,
        ]);

        return redirect()->route('admin.booking')->with('success', 'Booking updated successfully.');
    }


    public function destroyBooking($id)
    {
        Log::channel('booking')->info('Proses penghapusan booking dimulai.', ['booking_id' => $id]);

        // Cari booking berdasarkan ID
        $booking = Booking::findOrFail($id);

        // Hanya kurangi registered_count jika booking telah dibayar
        $class = Classes::findOrFail($booking->class_id);
        if ($booking->paid) {
            if ($class->registered_count > 0) {
                $class->decrement('registered_count');
            }
        }

        // Hapus booking
        $booking->delete();

        // Logging suksesnya penghapusan booking
        Log::channel('booking')->info('Booking berhasil dihapus.', [
            'booking_id' => $id,
            'class_id' => $booking->class_id,
        ]);

        return redirect()->route('admin.booking')->with('success', 'Booking deleted successfully.');
    }

    public function validateAttendance(Request $request)
    {
        $bookingCode = $request->input('booking_code');

        // Cari booking berdasarkan booking code
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Invalid booking code.');
        }

        // Cek apakah sudah absen
        if ($booking->attended) {
            return redirect()->back()->with('error', 'This booking has already been used for attendance.');
        }

        // Tandai bahwa booking telah digunakan untuk absen
        $booking->attended = true;
        $booking->save();

        Log::channel('booking')->info('Absensi berhasil dilakukan.', [
            'booking_id' => $booking->id,
            'booking_code' => $bookingCode,
        ]);

        return redirect()->back()->with('success', 'Attendance successfully recorded.');
    }
    public function getAvailableDates(Request $request)
    {
        $classId = $request->query('class_id');

        $class = Classes::findOrFail($classId);
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $dayMapping = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        $availableDates = [];
        $dayOfWeek = $dayMapping[$class->day_of_week] ?? null;

        if ($dayOfWeek !== null) {
            for ($day = 1; $day <= Carbon::now()->daysInMonth; $day++) {
                $date = Carbon::create($currentYear, $currentMonth, $day);
                if ($date->dayOfWeek === $dayOfWeek) {
                    $availableDates[] = $date->format('Y-m-d');
                }
            }
        }

        return response()->json(['availableDates' => $availableDates]);
    }


    public function createCoachBooking()
    {
        Log::channel('booking')->info('Menyiapkan halaman untuk membuat booking coach.');

        $today = Carbon::today();
        $coaches = User::where('role', 'coach')
            ->where('status', 'approved')
            ->where(function ($query) use ($today) {
                $query->whereNotExists(function ($query) use ($today) {
                    $query->select('id')
                        ->from('coach_bookings')
                        ->where('coach_id', '=', 'users.id')
                        ->whereDate('booking_date', $today)
                        ->where('availability_status', false);
                });
            })
            ->get();

        Log::channel('booking')->info('Data coach berhasil diambil.', ['coaches_count' => $coaches->count()]);

        return view('admin.bookings.createCoach', compact('coaches'));
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
            return redirect()->back()->with('error', 'Coach is already booked for the selected date and time.')->withInput();
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
        $dayOfWeekEnglish = $bookingDate->format('l'); // Ambil nama hari dalam bahasa Inggris (contoh: 'Monday')
        $dayOfWeek = $daysOfWeek[$dayOfWeekEnglish]; // Ubah ke bahasa Indonesia (contoh: 'Senin')

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
            return redirect()->back()->with('error', 'Coach has a class scheduled during the selected time.')->withInput();
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
        CoachBooking::create([
            'coach_id' => $coachId,
            'user_id' => $userId,
            'session_count' => $newSessionCount,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'start_booking_time' => $startBookingTime,
            'end_booking_time' => $endBookingTime,
            'booking_code' => $bookingCode,
            'payment_required' => $paymentRequired,
        ]);

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

        return redirect()->route('admin.booking')->with('success', 'Coach booked successfully!');
    }


    public function editCoachBooking($id)
    {
        Log::channel('booking')->info('Mengambil data booking coach untuk diedit.', ['booking_id' => $id]);

        $booking = CoachBooking::findOrFail($id);
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get();

        // Convert booking_date to Carbon instance
        $booking->booking_date = Carbon::parse($booking->booking_date);

        Log::channel('booking')->info('Data booking coach berhasil diambil.', [
            'booking_id' => $booking->id,
            'coach_id' => $booking->coach_id,
        ]);

        return view('admin.bookings.editCoach', compact('booking', 'coaches'));
    }


    public function updateCoachBooking(Request $request, $id)
    {
        Log::channel('booking')->info('Proses update booking coach dimulai.', ['booking_id' => $id]);

        // Validasi input dari request
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'session_count' => 'required|integer|min:1',
            'payment_required' => 'required|boolean',
            'booking_date' => 'required|date',
            'start_booking_time' => 'required|date_format:H:i',
            'end_booking_time' => 'required|date_format:H:i',
        ]);

        $booking = CoachBooking::findOrFail($id);

        // Update informasi booking
        $booking->coach_id = $request->input('coach_id');
        $booking->session_count = $request->input('session_count');
        $booking->payment_required = $request->input('payment_required');
        $booking->booking_date = Carbon::parse($request->input('booking_date'));
        $booking->start_booking_time = Carbon::parse($request->input('start_booking_time'));
        $booking->end_booking_time = Carbon::parse($request->input('end_booking_time'));
        $booking->save();

        Log::channel('booking')->info('Booking coach berhasil diupdate.', [
            'booking_id' => $booking->id,
            'coach_id' => $booking->coach_id,
            'session_count' => $booking->session_count,
            'payment_required' => $booking->payment_required,
            'booking_date' => $booking->booking_date,
            'start_booking_time' => $booking->start_booking_time,
            'end_booking_time' => $booking->end_booking_time,
        ]);


        return redirect()->route('admin.booking')->with('success', 'Coach booking updated successfully.');
    }

    public function deleteCoachBooking($id)
    {
        Log::channel('booking')->info('Menghapus booking coach.', ['booking_id' => $id]);

        $coachBooking = CoachBooking::findOrFail($id);

        $coachBooking->delete();


        Log::channel('booking')->info('Booking coach berhasil dihapus.', ['booking_id' => $id]);

        return redirect()->back()->with('success', 'Coach booking deleted successfully.');
    }

    public function manageAttendance(Request $request)
    {
        $search = $request->input('search');

        Log::channel('attendance')->info('Admin Melihat Absensi dengan Pencarian.', ['search' => $search]);

        // Query untuk Coach Attendances
        $attendancesQuery = Attendance::with(['class', 'coach']);
        if ($search) {
            $attendancesQuery->where(function ($q) use ($search) {
                $q->whereHas('coach', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('class', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('attendance_date', 'like', "%{$search}%");
            });
        }
        $attendances = $attendancesQuery->get();

        // Query untuk Member Attendances
        $memberAttendancesQuery = MemberAttendance::with(['booking', 'member', 'coach']);
        if ($search) {
            $memberAttendancesQuery->where(function ($q) use ($search) {
                $q->whereHas('member', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('coach', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('attendance_date', 'like', "%{$search}%");
            });
        }
        $memberAttendances = $memberAttendancesQuery->get();

        return view('admin.attendance', compact('attendances', 'memberAttendances'));
    }


    public function createAttendanceCoaches()
    {
        Log::channel('attendance')->info('Admin Telah Membuat Attendance Coach Baru');

        $coaches = User::where('role', 'coach')->get();
        $classes = Classes::all();
        Log::channel('attendance')->info('Daftar coaches:', $coaches->toArray());
        Log::channel('attendance')->info('Daftar classes:', $classes->toArray());

        return view('admin.attendances.create', compact('coaches', 'classes'));
    }

    public function storeAttendanceCoaches(Request $request)
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

        Attendance::create($attendanceData);

        Log::channel('attendance')->info('Membuat catatan absensi baru:', $attendanceData);

        return redirect()->route('admin.attendance')->with('success', 'Attendance created successfully.');
    }

    public function editAttendanceCoaches($id)
    {
        Log::channel('attendance')->info('Admin edit Attendance Record dengan ID: ' . $id);
        $attendance = Attendance::findOrFail($id);
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get();
        $classes = Classes::all();
        Log::channel('attendance')->info('Attendance record details:', $attendance->toArray());
        Log::channel('attendance')->info('Daftar coaches:', $coaches->toArray());
        Log::channel('attendance')->info('Daftar classes:', $classes->toArray());

        return view('admin.attendances.edit', compact('attendance', 'coaches', 'classes'));
    }

    public function updateAttendanceCoaches(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:Present,Sick,Excused,Absent',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'absence_reason' => 'nullable|string',
            'unique_code' => 'nullable|string',
        ]);

        $attendance = Attendance::findOrFail($id);

        $updatedData = [
            'class_id' => $request->class_id ?: null,
            'user_id' => $request->user_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'absence_reason' => $request->absence_reason,
            'unique_code' => $request->unique_code,
        ];

        $attendance->update($updatedData);

        Log::channel('attendance')->info('Updated attendance record with ID: ' . $id, $updatedData);

        return redirect()->route('admin.attendance')->with('success', 'Attendance updated successfully.');
    }
    public function destroyAttendanceCoaches($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendanceData = $attendance->toArray();
        $attendance->delete();

        Log::channel('attendance')->info('Menghapus attendance record with ID: ' . $id, $attendanceData);

        return redirect()->route('admin.attendance')->with('success', 'Attendance deleted successfully.');
    }


    public function createMemberAttendance()
    {
        $bookings = CoachBooking::whereHas('coach')->whereHas('user')->get();
        return view('admin.attendances.createAttmember', compact('bookings'));
    }

    public function storeMemberAttendance(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:coach_bookings,id',
            'member_id' => 'required|exists:users,id',
            'coach_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
        ]);

        $qrCodeData = 'MMB-' . $request->member_id . '-BK-' . $request->booking_id;
        $qrCodePath = public_path('qrcodes/QR-' . $qrCodeData . '.png');
        if (!File::exists(public_path('qrcodes'))) {
            File::makeDirectory(public_path('qrcodes'), 0755, true);
        }
        QrCode::format('png')->size(300)->generate($qrCodeData, $qrCodePath);

        MemberAttendance::create(array_merge($request->all(), ['unique_code' => $qrCodeData]));

        Log::channel('attendance')->info('Member Attendance created successfully with QR Code.', ['qr_code' => $qrCodeData]);

        return redirect()->route('admin.attendance')->with('success', 'Member Attendance created successfully with QR Code.');
    }

    public function editMemberAttendance($id)
    {
        $attendance = MemberAttendance::findOrFail($id);
        $bookings = CoachBooking::whereHas('coach')->whereHas('user')->get();

        return view('admin.attendances.editAttMember', compact('attendance', 'bookings'));
    }

    public function updateMemberAttendance(Request $request, $id)
    {
        $request->validate([
            'booking_id' => 'required|exists:coach_bookings,id',
            'member_id' => 'required|exists:users,id',
            'coach_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:Present,Absent,Not Yet',
        ]);

        $attendance = MemberAttendance::findOrFail($id);
        $attendance->update($request->all());

        Log::channel('attendance')->info('Updated member attendance record with ID: ' . $id);

        return redirect()->route('admin.attendance')->with('success', 'Member Attendance updated successfully.');
    }

    public function destroyMemberAttendance($id)
    {
        $attendance = MemberAttendance::findOrFail($id);
        $attendance->delete();

        Log::channel('attendance')->info('Deleted member attendance record with ID: ' . $id);

        return redirect()->route('admin.attendance')->with('success', 'Member Attendance deleted successfully.');
    }

    public function scanQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $attendance = MemberAttendance::where('unique_code', $request->qr_code)->first();

        if (!$attendance) {
            return back()->with('error', 'QR Code tidak valid');
        }

        if ($attendance->status == 'Present') {
            return back()->with('info', 'Absensi sudah tercatat sebagai hadir.');
        }

        $attendance->update(['status' => 'Present']);

        Log::channel('attendance')->info('QR Code scanned and attendance updated for code: ' . $request->qr_code);

        return back()->with('success', 'Absensi berhasil diperbarui.');
    }
    public function manageCategory(Request $request)
    {
        // Mendapatkan keyword dari input search
        $search = $request->input('search');

        // Jika ada keyword pencarian, ambil kategori yang cocok
        if ($search) {
            $categories = Category::where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->get();
        } else {
            // Jika tidak ada pencarian, ambil semua kategori
            $categories = Category::all();
        }

        // Tampilkan halaman daftar kategori dengan data
        return view('admin.category', compact('categories', 'search'));
    }

    public function createCategory()
    {
        return view('admin.categories.create'); // Tampilkan form tambah kategori
    }
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->all());

        // Logging aksi penambahan kategori
        Log::channel('category')->info('Category added: ', ['id' => $category->id, 'name' => $category->name]);

        return redirect()->route('admin.category')->with('success', 'Kategori berhasil ditambahkan.');
    }
    public function editCategory(Category $category, $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category')); // Tampilkan form edit kategori
    }
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->all());

        // Logging aksi update kategori
        Log::channel('category')->info('Category updated: ', ['id' => $category->id, 'name' => $category->name, 'description' => $category->description]);

        return redirect()->route('admin.category')->with('success', 'Category updated successfully.');
    }
    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        // Logging aksi penghapusan kategori
        Log::channel('category')->info('Category deleted: ', ['id' => $category->id, 'name' => $category->name]);

        return redirect()->route('admin.category')->with('success', 'Category deleted successfully.');
    }
    public function manageRoom(Request $request)
    {
        $search = $request->input('search');
        $rooms = Room::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('equipment', 'like', "%{$search}%")
                ->orWhere('capacity', $search);
        })->get();

        return view('admin.rooms', compact('rooms', 'search'));
    }

    public function createRoom()
    {
        return view('admin.room.create');
    }
    public function storeRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:rooms,name',
            'capacity' => 'required|integer',
            'equipment' => 'required|string|max:255',
        ]);

        Room::create($request->all());

        return redirect()->route('admin.rooms')->with('success', 'Room created successfully.');
    }
    public function editRoom($id)
    {
        $room = Room::findOrFail($id);
        return view('admin.room.edit', compact('room'));
    }
    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50|unique:rooms,name,' . $room->id,
            'capacity' => 'required|integer',
            'equipment' => 'nullable|string|max:255',
        ]);

        $room->update($request->all());

        return redirect()->route('admin.rooms')->with('success', 'Room updated successfully.');
    }

    // Menghapus ruangan
    public function destroyRoom($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return redirect()->route('admin.rooms')->with('success', 'Room deleted successfully.');
    }
    public function showLogs()
    {
        // Baca file log
        $logFilePath = storage_path('logs/classes.log');
        $logs = [];

        if (File::exists($logFilePath)) {
            $logs = File::get($logFilePath);
        }

        // Kirim log ke view
        return view('admin.classes.logs', compact('logs'));
    }
    public function showUserLogs()
    {
        // Path ke file log user
        $logFilePath = storage_path('logs/user.log');

        // Mengecek apakah file log ada
        if (File::exists($logFilePath)) {
            // Membaca isi file log
            $logs = File::get($logFilePath);
        } else {
            $logs = 'Log file not found or is empty.';
        }

        // Mengirim log ke view
        return view('admin.users.logs', compact('logs'));
    }
    public function showBookingLogs()
    {
        // Path ke file log user
        $logFilePath = storage_path('logs/booking.log');

        // Mengecek apakah file log ada
        if (File::exists($logFilePath)) {
            // Membaca isi file log
            $logs = File::get($logFilePath);
        } else {
            $logs = 'Log file not found or is empty.';
        }

        // Mengirim log ke view
        return view('admin.bookings.logs', compact('logs'));
    }
    public function showAttendanceLogs()
    {
        $logFilePath = storage_path('logs/attendance.log');

        if (File::exists($logFilePath)) {
            $logs = File::get($logFilePath);
        } else {
            $logs = 'Log file is empty or not found.';
        }

        return view('admin.attendances.logs', compact('logs'));
    }
    public function showCategoryLogs()
    {
        $logFilePath = storage_path('logs/category.log');

        if (File::exists($logFilePath)) {
            $logs = File::get($logFilePath);
        } else {
            $logs = 'Log file is empty or not found.';
        }

        return view('admin.categories.logs', compact('logs'));
    }
    public function showRoomLogs()
    {
        $logFilePath = storage_path('logs/rooms.log');

        if (File::exists($logFilePath)) {
            $logs = File::get($logFilePath);
        } else {
            $logs = 'Log file is empty or not found.';
        }

        return view('admin.room.logs', compact('logs'));
    }
    public function getClasses()
    {
        // Ambil data coach yang sedang login
        $coach = Auth::user();

        // Ambil kelas yang harus diajar oleh coach
        $classes = Classes::where('coach_id', $coach->id)->with('category')->get();

        // Format data untuk API
        $events = $classes->map(function ($class) {
            return [
                'name' => $class->name,
                'date' => Carbon::parse($class->date)->format('Y-m-d'), // Menggunakan Carbon untuk mengonversi dan format
                'day_of_week' => Carbon::parse($class->date)->format('l'), // Menggunakan Carbon untuk mengonversi dan mendapatkan hari
                'category_id' => $class->category_id ? $class->category->name : 'No Category', // Mengambil nama kategori
                'start_time' => Carbon::parse($class->date . ' ' . $class->start_time)->format('Y-m-d\TH:i:s'), // Gabungkan tanggal dan waktu mulai
                'end_time' => Carbon::parse($class->date . ' ' . $class->end_time)->format('Y-m-d\TH:i:s'), // Gabungkan tanggal dan waktu selesai
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
    public function getPopularClasses()
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
}
