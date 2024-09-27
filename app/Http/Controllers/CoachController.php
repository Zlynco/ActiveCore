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




class CoachController extends Controller
{
    /**
     * Tampilkan halaman dashboard coach.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil data coach yang sedang login
        $coach = Auth::user();

        // Contoh: Ambil kelas yang harus diajar oleh coach
        $classes = Classes::where('coach_id', $coach->id)->get();

        // Kirim data ke view dashboard coach
        return view('coach.dashboard', [
            'coach' => $coach,
            'classes' => $classes,
        ]);
    }
    public function coachClasses(Request $request)
    {
        // Ambil user yang sedang login
        $coach = auth()->user();

        // Ambil kelas yang diajar oleh coach ini
        $classes = $coach->classes;
        return view('coach.kelas', compact('classes'));
    }

    public function showCoachBookings(Request $request)
{
    // Ambil user yang sedang login
    $coach = auth()->user();

    // Ambil parameter pencarian dari query string
    $search = $request->input('search');

    // Ambil booking yang dibuat oleh member untuk coach ini
    $query = CoachBooking::where('coach_id', $coach->id);

    if ($search) {
        // Filter berdasarkan kode booking atau nama member
        $query->where(function ($q) use ($search) {
            $q->where('booking_code', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    // Urutkan data berdasarkan booking_date dan booking_time secara descending (terbaru ke terlama)
    $bookings = $query->orderBy('booking_date', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();

    // Konversi booking_date dan booking_time menjadi Carbon object
    foreach ($bookings as $booking) {
        $booking->booking_date = Carbon::parse($booking->booking_date);
        $booking->booking_time = Carbon::parse($booking->booking_time);
    }

    return view('coach.booking', compact('bookings'));
}


    public function coachAbsen(Request $request)
    {
        $coach = Auth::user();

        // Ambil kelas yang diajar oleh coach
        $classes = $coach->classes;

        // Jika coach tidak memiliki kelas, tambahkan pilihan "Tanpa Kelas"
        $classesList = $classes->isEmpty() ? collect(['No Class' => 'No Class']) : $classes->pluck('name', 'id');
        return view('coach.attendance', compact('classes', 'classesList'));
    }
    public function storeAttendance(Request $request)
{
    // Validasi input
    $request->validate([
        'class_id' => 'nullable|string',
        'attendance_date' => 'required|date',
        'status' => 'required|in:Present,Sick,Excused,Absent',
        'check_in' => 'nullable|date_format:H:i',
        'check_out' => 'nullable|date_format:H:i',
        'absence_reason' => 'nullable|string',
    ]);

    // Cek apakah sudah ada absensi untuk kelas dan tanggal yang sama
    $existingAttendance = Attendance::where('user_id', Auth::id())
        ->where('class_id', $request->class_id === 'No Class' ? null : $request->class_id)
        ->where('attendance_date', $request->attendance_date)
        ->first();

    if ($existingAttendance) {
        return response()->json([
            'success' => false,
            'message' => 'Attendance for this class on this date has already been recorded.',
        ]);
    }

    // Buat kode unik untuk absensi
    $uniqueCode = 'ATT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    try {
        // Simpan absensi
        Attendance::create([
            'user_id' => Auth::id(),
            'class_id' => $request->class_id === 'No Class' ? null : $request->class_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'absence_reason' => $request->absence_reason,
            'unique_code' => $uniqueCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'redirect' => route('coach.attendance'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to record attendance. ' . $e->getMessage(),
        ]);
    }
}
public function showMemberAttendance()
{
    // Mengambil data attendance member yang berelasi
    $memberAttendances = MemberAttendance::with(['booking', 'member', 'coach'])
        ->whereHas('booking', function ($query) {
            $query->where('coach_id', auth()->user()->id); // Hanya mengambil booking untuk coach yang login
        })
        ->get();

    return view('coach.memberatt', compact('memberAttendances')); // Pastikan variabel dikirim
}
public function createMemberAttendance()
{
    // Mengambil booking yang terkait dengan coach yang sedang login
    $bookings = CoachBooking::whereHas('coach', function ($query) {
        $query->where('id', auth()->id());
    })->whereHas('user')->get();

    return view('coach.attendances.create', compact('bookings'));
}

public function storeMemberAttendance(Request $request)
{
    $request->validate([
        'booking_id' => 'required|exists:coach_bookings,id',
        'member_id' => 'required|exists:users,id',
        'attendance_date' => 'required|date',
    ]);

    $qrCodeData = 'member-' . $request->member_id . '-booking-' . $request->booking_id;
    $qrCodePath = public_path('qrcodes/QR-' . $qrCodeData . '.png');

    // Membuat direktori jika belum ada
    if (!File::exists(public_path('qrcodes'))) {
        File::makeDirectory(public_path('qrcodes'), 0755, true);
    }

    // Menghasilkan QR Code
    QrCode::format('png')->size(300)->generate($qrCodeData, $qrCodePath);

    // Menyimpan absensi member ke database dengan coach_id dari yang sedang login
    MemberAttendance::create(array_merge($request->all(), [
        'unique_code' => $qrCodeData,
        'coach_id' => Auth::id(), // Menambahkan coach_id dari user yang sedang login
    ]));

    Log::channel('attendance')->info('Member Attendance created successfully with QR Code.', ['qr_code' => $qrCodeData]);

    return redirect()->route('coach.memberAttendance')->with('success', 'Member Attendance created successfully with QR Code.');
}

public function editMemberAttendance($id)
{
    $attendance = MemberAttendance::findOrFail($id);

    // Mengambil booking untuk coach yang sedang login
    $bookings = CoachBooking::whereHas('coach', function ($query) {
        $query->where('id', auth()->id());
    })->whereHas('user')->get();

    return view('coach.attendances.edit', compact('attendance', 'bookings'));
}

// Method untuk memperbarui data absensi member
public function updateMemberAttendance(Request $request, $id)
{
    $request->validate([
        'booking_id' => 'required|exists:coach_bookings,id',
        'attendance_date' => 'required|date',
        'status' => 'required|in:Present,Absent,Not Yet',
    ]);

    $attendance = MemberAttendance::findOrFail($id);
    
    // Hanya memperbarui booking_id, attendance_date, dan status
    $attendance->update($request->only('booking_id', 'attendance_date', 'status'));

    Log::channel('attendance')->info('Updated member attendance record with ID: ' . $id);

    return redirect()->route('coach.memberAttendance')->with('success', 'Member Attendance updated successfully.');
}


// Method untuk menghapus absensi member
public function destroyMemberAttendance($id)
{
    $attendance = MemberAttendance::findOrFail($id);
    $attendance->delete();

    Log::channel('attendance')->info('Deleted member attendance record with ID: ' . $id);

    return redirect()->route('coach.memberAttendance')->with('success', 'Member Attendance deleted successfully.');
}

    // Method untuk memindai QR Code dan memperbarui absensi
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
    
    public function checkAvailability($coachId, $date)
    {
        // Temukan coach berdasarkan ID
        $coach = User::findOrFail($coachId);

        // Periksa apakah coach memiliki kelas atau booking pada tanggal yang diberikan
        $hasClassOnDate = $coach->classes()->whereDate('date', $date)->exists();
        $hasBookingOnDate = $coach->bookings()->whereDate('date', $date)->exists();

        return !$hasClassOnDate && !$hasBookingOnDate;
    }
    public function getClasses()
    {
        // Ambil data coach yang sedang login
        $coach = Auth::user();
    
        // Ambil kelas yang harus diajar oleh coach
        $classes = Classes::where('coach_id', $coach->id)->get();
    
        // Format data untuk API
        $events = [];
    
        foreach ($classes as $class) {
            // Mendapatkan tanggal kelas berikutnya
            $nextClassDate = $this->getNextClassDate($class->day_of_week);
    
            // Menghitung start dan end berdasarkan tanggal kelas berikutnya
            $events[] = [
                'title' => $class->name,
                'start' => $nextClassDate->toDateString() . ' ' . $class->start_time,
                'end' => $nextClassDate->toDateString() . ' ' . $class->end_time,
                'quota' => $class->quota,
            ];
        }
    
        return response()->json($events);
    }
    
    
}
