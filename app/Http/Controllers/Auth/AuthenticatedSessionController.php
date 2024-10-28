<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User as ModelsUser;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi dan autentikasi pengguna
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Autentikasi pengguna
        if (Auth::attempt($request->only('email', 'password'))) {
            // Regenerasi session untuk mencegah session fixation
            $request->session()->regenerate();

            // Ambil data pengguna yang sedang login
            $user = ModelsUser::find(Auth::id());

            // Cek role dan status pengguna
            if ($user->role === 'coach') {
                // Cek status pengguna
                if ($user->status === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your coach registration is pending approval. Please wait until an admin approves your registration.',
                    ]);
                } elseif ($user->status === 'rejected') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->withErrors([
                        'email' => 'Your coach registration has been rejected. Please contact support for more information.',
                    ]);
                }

                // Jika coach diterima, buat token
                $token = $user->createToken('authTokenCoach')->plainTextToken;
                Log::channel('token')->info('Generated Token for Coach: ' . $token); // Log token untuk coach
                return redirect()->route('coach.dashboard');
            }

            // Arahkan pengguna berdasarkan perannya
            if ($user->role === 'admin') {
                $token = $user->createToken('authTokenAdmin')->plainTextToken;
                Log::channel('token')->info('Generated Token for Admin: ' . $token); // Log token untuk admin
                return redirect()->route('admin.dashboard');
            }

            // Redirect untuk pengguna biasa (member)
            $token = $user->createToken('authTokenMember')->plainTextToken;
            Log::channel('token')->info('Generated Token for Member: ' . $token); // Log token untuk member
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // Jika autentikasi gagal
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Ambil pengguna yang sedang login
        $user = ModelsUser::find(Auth::id());

        // Hapus semua token yang terkait dengan pengguna ini
        $user->tokens()->delete();

        // Logout dari sesi
        Auth::logout();

        // Menghapus sesi dan regenerasi token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('/admin/dashboard');
        }

        return redirect()->route('dashboard'); // Redirect untuk user biasa
    }
}
