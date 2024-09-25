<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Booking;
use App\Models\Classes;
use App\Models\ClassLog;
use App\Models\Coach;
use App\Models\CoachBooking;
use App\Models\MemberAttendance;
use App\Models\PendingRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function editCoach($id)
    {
        Log::channel('userlog')->info('Mengambil data coach untuk di-edit.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
        return view('admin.edit-coach', compact('coach'));
    }

    public function updateCoach(Request $request, $id)
    {
        Log::channel('userlog')->info('Proses update coach dimulai.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
        $coach->update($request->all());

        Log::channel('userlog')->info('Coach berhasil diupdate.', ['coach_id' => $id]);
        return redirect()->route('admin.user')->with('success', 'Coach updated successfully');
    }

    public function deleteCoach($id)
    {
        Log::channel('userlog')->info('Menghapus coach.', ['coach_id' => $id]);
        $coach = User::findOrFail($id);
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

        Log::channel('classes')->info('Mengambil data kelas dengan pencarian.', ['search' => $search]);

        // Buat query untuk kelas
        $classesQuery = Classes::with('coach'); // Pastikan 'coach' adalah relasi yang benar

        if ($search) {
            $classesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('day_of_week', 'like', "%{$search}%")
                    ->orWhere('time', 'like', "%{$search}%");
            });
        }

        $classes = $classesQuery->get();

        Log::channel('classes')->info('Data kelas berhasil diambil.', ['classes_count' => $classes->count()]);

        return view('admin.kelas', compact('classes'));
    }

    public function editClass($id)
    {
        Log::channel('classes')->info('Mengambil data kelas.', ['timestamp' => now()]);
        $class = Classes::findOrFail($id);
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get();

        Log::channel('classes')->info('Data coach berhasil diambil.', ['coaches_count' => $coaches->count()]);
        return view('admin.classes.edit', compact('class', 'coaches'));
    }

    public function updateClass(Request $request, $id)
    {
        Log::channel('classes')->info('Proses update kelas dimulai.', ['class_id' => $id]);

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'day_of_week' => 'required|string',
            'time' => 'nullable|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'coach_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'quota' => 'required|integer|min:1',
        ]);

        // Ambil data kelas
        $class = Classes::findOrFail($id);
        $originalData = $class->toArray(); // Simpan data asli untuk log

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
            'time' => $request->time,
            'price' => $request->price,
            'coach_id' => $request->coach_id,
            'image' => $imagePath,
            'quota' => $request->quota,
        ]);

        Log::channel('classes')->info('Kelas berhasil diperbarui.', [
            'class_id' => $class->id,
            'original_data' => $originalData,
            'updated_data' => $class->toArray()
        ]);

        // Update availability status
        $this->updateCoachAvailabilityClass($request->coach_id, $request->day_of_week);

        return redirect()->route('admin.kelas')->with('success', 'Class updated successfully.');
    }

    public function deleteClass($id)
{
    Log::channel('classes')->info('Menghapus kelas.', ['class_id' => $id]);

    $class = Classes::findOrFail($id);

    // Hapus gambar jika ada
    if ($class->image) {
        Storage::delete($class->image); // Hapus gambar dari storage
        Log::channel('classes')->info('Gambar kelas dihapus.', ['class_id' => $id, 'image_path' => $class->image]);
    }

    // Hapus kelas dari database
    $class->delete();

    // Cek apakah masih ada kelas yang dijadwalkan untuk coach pada hari yang sama
    $hasClassToday = Classes::where('coach_id', $class->coach_id)
        ->where('day_of_week', $class->day_of_week)
        ->exists();

    // Update availability status coach berdasarkan ada atau tidaknya kelas
    if ($hasClassToday) {
        // Jika masih ada kelas, tetap unavailable
        User::where('id', $class->coach_id)->update(['availability_status' => 0]);
    } else {
        // Jika tidak ada kelas, set coach menjadi available
        User::where('id', $class->coach_id)->update(['availability_status' => 1]);
    }

    Log::channel('classes')->info('Kelas berhasil dihapus.', ['class_id' => $id]);

    return redirect()->route('admin.kelas')->with('success', 'Class deleted successfully.');
}


    public function createClass()
    {
        Log::channel('classes')->info('Menyiapkan halaman untuk membuat kelas baru.');
        $coaches = User::where('role', 'coach')->where('status', 'approved')->get();

        Log::channel('classes')->info('Data coach berhasil diambil untuk pembuatan kelas baru.', ['coaches_count' => $coaches->count()]);
        return view('admin.classes.create', compact('coaches'));
    }

    public function storeClass(Request $request)
    {
        Log::channel('classes')->info('Proses pembuatan kelas baru dimulai.');

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'day_of_week' => 'required|string',
            'time' => 'required|date_format:H:i',
            'price' => 'required|numeric|min:0',
            'coach_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'quota' => 'required|integer|min:1',
        ]);

        // Unggah gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            Log::channel('classes')->info('Mengunggah gambar untuk kelas baru.');
            $imagePath = $request->file('image')->store('public/images'); // Simpan gambar baru
        }

        // Simpan kelas baru
        $class = Classes::create([
            'name' => $request->name,
            'description' => $request->description,
            'day_of_week' => $request->day_of_week,
            'time' => $request->time,
            'price' => $request->price,
            'coach_id' => $request->coach_id,
            'image' => $imagePath,
            'quota' => $request->quota,
        ]);

        Log::channel('classes')->info('Kelas baru berhasil disimpan.', [
            'class_id' => $class->id,
            'data' => $class->toArray()
        ]);

        // Update availability status
        $this->updateCoachAvailabilityClass($request->coach_id, $request->day_of_week);

        return redirect()->route('admin.kelas')->with('success', 'Class added successfully.');
    }


    private function updateCoachAvailabilityClass($coach_id, $day_of_week)
    {
        $today = Carbon::now(); // Hari ini
        $todayDayOfWeek = $today->format('l'); // Ambil nama hari dalam format lengkap
    
        // Cek apakah ada kelas untuk coach pada hari ini
        $hasClassToday = Classes::where('coach_id', $coach_id)
            ->where('day_of_week', $todayDayOfWeek)
            ->exists();
    
        // Jika ada kelas hari ini, set coach menjadi unavailable
        if ($hasClassToday) {
            User::where('id', $coach_id)->update(['availability_status' => 0]);
        } else {
            // Cek apakah ada kelas yang dijadwalkan di masa depan
            $nextClassDate = $this->getNextClassDate($day_of_week);

    
            // Jika tidak ada kelas hari ini dan ada kelas di masa depan, set coach menjadi available
            if ($today->isSameDay($nextClassDate)) {
                User::where('id', $coach_id)->update(['availability_status' => 0]);
            } else {
                User::where('id', $coach_id)->update(['availability_status' => 1]);
            }
        }
    }
    

    public function updateCoachAvailabilityBooking($coach_id, $bookingDate = null)
{
    // Cek apakah coach_id dan bookingDate tidak kosong
    if (empty($coach_id)) {
        Log::channel('booking')->warning('Coach ID tidak diberikan.', ['coach_id' => $coach_id]);
        return;
    }

    // Format tanggal untuk pencarian
    $bookingDate = $bookingDate ? Carbon::parse($bookingDate)->format('Y-m-d') : null;

    $coach = User::find($coach_id);

    if (!$coach) {
        Log::channel('booking')->warning('Coach tidak ditemukan.', ['coach_id' => $coach_id]);
        return;
    }

    // Cek apakah ada booking pada tanggal hari ini
    $hasBookingToday = CoachBooking::where('coach_id', $coach_id)
        ->whereDate('booking_date', now()->format('Y-m-d'))
        ->exists();

    // Cek apakah ada booking di masa depan
    $hasUpcomingBooking = CoachBooking::where('coach_id', $coach_id)
        ->whereDate('booking_date', '>', now()->format('Y-m-d'))
        ->exists();

    // Update ketersediaan coach
    if ($hasBookingToday && $bookingDate === now()->format('Y-m-d')) {
        // Jika ada booking hari ini, tandai coach sebagai unavailable
        $coach->availability_status = 0;
    } elseif ($hasUpcomingBooking) {
        // Jika ada booking mendatang, tandai coach sebagai available
        $coach->availability_status = 1;
    } else {
        // Jika tidak ada booking, tandai coach sebagai available
        $coach->availability_status = 1;
    }

    $coach->save();

    Log::channel('booking')->info('Ketersediaan coach diperbarui.', [
        'coach_id' => $coach_id,
        'availability_status' => $coach->availability_status,
        'booking_date' => $bookingDate,
    ]);
}

    public function getNextClassDate($day_of_week)
    {
        $daysOfWeek = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        $today = Carbon::now();
        $targetDay = $daysOfWeek[$day_of_week];

        // Hitung hari ke depan dari hari ini ke hari target
        $daysUntilNextClass = ($targetDay - $today->dayOfWeek + 7) % 7;

        // Jika hari ini adalah hari kelas, kembalikan hari ini
        return $today->addDays($daysUntilNextClass);
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

        // Periksa apakah kode sudah ada di database
        while (CoachBooking::where('booking_code', $code)->exists()) {
            $nextId++;
            $code = $prefix . '-' . now()->format('Ymd') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }

        return $code;
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

        $bookings = $bookingsQuery->get();
        $coachBookings = $coachBookingsQuery->get();

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
                'time' => $booking->class->time,
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

        return view('admin.booking', compact('bookings', 'coachBookings'));
    }

    public function createBooking()
    {
        Log::channel('booking')->info('Menyiapkan halaman untuk membuat booking baru.');

        $classes = Classes::all();
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

        $classesWithDates = [];
        foreach ($classes as $class) {
            $classDates = [];
            $dayOfWeek = $dayMapping[$class->day_of_week] ?? null;

            if ($dayOfWeek !== null) {
                for ($day = 1; $day <= Carbon::now()->daysInMonth; $day++) {
                    $date = Carbon::create($currentYear, $currentMonth, $day);
                    if ($date->dayOfWeek === $dayOfWeek) {
                        $formattedDate = $date->format('Y-m-d');

                        // Hitung kuota yang tersedia untuk tanggal ini
                        $currentBookings = Booking::where('class_id', $class->id)
                            ->whereDate('booking_date', $formattedDate)
                            ->where('paid', true)
                            ->count();
                        $availableQuota = $class->quota - $currentBookings;

                        $classDates[] = [
                            'date' => $formattedDate,
                            'available_quota' => $availableQuota
                        ];
                    }
                }
            }

            $classesWithDates[] = [
                'class' => $class,
                'availableDates' => $classDates,
            ];
        }

        Log::channel('booking')->info('Data kelas dan tanggal tersedia berhasil dipersiapkan.', [
            'classes_count' => count($classesWithDates),
            'date_generated' => Carbon::now()->toDateTimeString()
        ]);

        return view('admin.bookings.create', compact('classesWithDates'));
    }
    public function storeBooking(Request $request)
    {
        Log::channel('booking')->info('Proses penyimpanan booking dimulai.');

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'booking_date' => 'required|date',
        ]);

        $class = Classes::findOrFail($request->class_id);
        $bookingDate = Carbon::parse($request->booking_date);

        // Hitung kuota yang tersedia untuk tanggal yang dipilih
        $currentBookings = Booking::where('class_id', $class->id)
            ->whereDate('booking_date', $bookingDate)
            ->where('paid', true)
            ->count();

        $availableQuota = $class->quota - $currentBookings;

        if ($availableQuota <= 0) {
            return redirect()->back()->with('quota_full', true);
        }

        // Buat booking baru
        $nextBookingId = Booking::max('id') + 1;
        $bookingCode = $this->generateBookingCode('CLS', $nextBookingId);

        Booking::create([
            'class_id' => $request->class_id,
            'user_id' => Auth::id(),
            'booking_date' => $bookingDate,
            'booking_code' => $bookingCode,
            'amount' => $class->price,
            'paid' => false,
        ]);

        Log::channel('booking')->info('Booking baru berhasil disimpan.', ['booking_code' => $bookingCode]);

        return redirect()->route('admin.booking')->with('success', 'Booking added successfully.');
    }
    public function editBooking($id)
    {
        Log::channel('booking')->info('Mengambil data booking untuk diedit.', ['booking_id' => $id]);

        $booking = Booking::findOrFail($id);
        $classes = Classes::all();
        $members = User::where('role', 'member')->get();

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
        foreach ($classes as $class) {
            if ($class->id === $booking->class_id) {
                $dayOfWeek = $dayMapping[$class->day_of_week] ?? null;

                if ($dayOfWeek !== null) {
                    for ($day = 1; $day <= Carbon::now()->daysInMonth; $day++) {
                        $date = Carbon::create($currentYear, $currentMonth, $day);
                        if ($date->dayOfWeek === $dayOfWeek) {
                            $availableDates[] = $date->format('Y-m-d');
                        }
                    }
                }
            }
        }

        Log::channel('booking')->info('Tanggal tersedia untuk booking berhasil dipersiapkan.', ['available_dates_count' => count($availableDates)]);
        return view('admin.bookings.edit', compact('booking', 'availableDates'));
    }
    public function updateBooking(Request $request, $id)
    {
        Log::channel('booking')->info('Proses update booking dimulai.', ['booking_id' => $id]);

        // Validasi input
        $request->validate([
            'booking_date' => 'required|date_format:Y-m-d',
        ]);
        $booking = Booking::findOrFail($id);
        $booking->booking_date = $request->booking_date;
        $booking->paid = $request->has('paid'); // Jika checkbox checked, maka set paid = true

        // Simpan perubahan
        $booking->save();

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

        $booking = Booking::findOrFail($id);
        $booking->delete();

        Log::channel('booking')->info('Booking berhasil dihapus.', ['booking_id' => $id]);

        return redirect()->route('admin.booking')->with('success', 'Booking deleted successfully.');
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
        'booking_time' => 'required|date_format:H:i',
    ]);

    $userId = Auth::id();
    $coachId = $request->input('coach_id');
    $bookingDate = Carbon::parse($request->input('booking_date'))->format('Y-m-d');
    $bookingTime = Carbon::parse($request->input('booking_time'));

    // Periksa ketersediaan coach
    $hasBooking = CoachBooking::where('coach_id', $coachId)
        ->whereDate('booking_date', $bookingDate)
        ->where('booking_time', $bookingTime)
        ->exists();

    if ($hasBooking) {
        return redirect()->back()->with('error', 'Coach is already booked for the selected date and time.')->withInput();
    }

    // Dapatkan hari dalam minggu dari tanggal booking
    $dayOfWeek = Carbon::parse($bookingDate)->locale('id')->translatedFormat('l');

    // Cek apakah coach memiliki kelas pada hari booking
    $hasClassOnBookingDate = Classes::where('coach_id', $coachId)
        ->where('day_of_week', $dayOfWeek)
        ->exists();

    if ($hasClassOnBookingDate) {
        return redirect()->back()->with('error', 'Coach is not available on this date due to a scheduled class.')->withInput();
    }

    // Cek ID booking terakhir dan buat kode booking baru
    $nextCoachBookingId = CoachBooking::max('id') + 1;
    $bookingCode = $this->generateBookingCode('CCH', $nextCoachBookingId);

    // Mendapatkan booking terakhir untuk user dan coach
    $existingBooking = CoachBooking::where('user_id', $userId)
        ->where('coach_id', $coachId)
        ->latest()
        ->first();

    // Penanganan session count
    $newSessionCount = $existingBooking ? $existingBooking->session_count + 1 : 1;
    $paymentRequired = $newSessionCount % 4 == 0;

    // Buat booking baru
    CoachBooking::create([
        'coach_id' => $coachId,
        'user_id' => $userId,
        'session_count' => $newSessionCount,
        'booking_date' => $bookingDate,
        'booking_time' => $bookingTime,
        'booking_code' => $bookingCode,
        'payment_required' => $paymentRequired,
    ]);

    Log::channel('booking')->info('Booking coach berhasil dibuat.', [
        'user_id' => $userId,
        'coach_id' => $coachId,
        'session_count' => $newSessionCount,
        'payment_required' => $paymentRequired,
        'booking_date' => $bookingDate,
        'booking_time' => $bookingTime->format('H:i'),
        'booking_code' => $bookingCode,
    ]);

    // Update availability status
    $this->updateCoachAvailabilityBooking($coachId, $bookingDate);

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
        'booking_time' => 'required|date_format:H:i',
    ]);

    $booking = CoachBooking::findOrFail($id);
    $oldCoachId = $booking->coach_id;
    $oldBookingDate = $booking->booking_date;

    // Update informasi booking
    $booking->coach_id = $request->input('coach_id');
    $booking->session_count = $request->input('session_count');
    $booking->payment_required = $request->input('payment_required');
    $booking->booking_date = Carbon::parse($request->input('booking_date'));
    $booking->booking_time = Carbon::parse($request->input('booking_time'));
    $booking->save();

    Log::channel('booking')->info('Booking coach berhasil diupdate.', [
        'booking_id' => $booking->id,
        'coach_id' => $booking->coach_id,
        'session_count' => $booking->session_count,
        'payment_required' => $booking->payment_required,
        'booking_date' => $booking->booking_date,
        'booking_time' => $booking->booking_time,
    ]);

    // Update availability status untuk coach yang baru dan yang lama
    $this->updateCoachAvailabilityBooking($oldCoachId, $oldBookingDate);
    $this->updateCoachAvailabilityBooking($booking->coach_id, $booking->booking_date);

    return redirect()->route('admin.booking')->with('success', 'Coach booking updated successfully.');
}

public function deleteCoachBooking($id)
{
    Log::channel('booking')->info('Menghapus booking coach.', ['booking_id' => $id]);

    $coachBooking = CoachBooking::findOrFail($id);
    $coachId = $coachBooking->coach_id;
    $bookingDate = $coachBooking->booking_date;

    $coachBooking->delete();

    // Periksa apakah ada booking lain pada tanggal yang sama
    $hasOtherBookings = CoachBooking::where('coach_id', $coachId)
        ->whereDate('booking_date', $bookingDate)
        ->exists();

    if (!$hasOtherBookings) {
        // Jika tidak ada booking lain, ubah status ketersediaan coach menjadi available
        $this->updateCoachAvailabilityBooking($coachId, $bookingDate);
    }

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

        $qrCodeData = 'member-' . $request->member_id . '-booking-' . $request->booking_id;
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
}
