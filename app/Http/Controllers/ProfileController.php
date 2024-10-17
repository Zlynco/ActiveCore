<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    
    public function editCoach(Request $request): View
    {
        return view('profile.editc', [
            'user' => $request->user(),
        ]);
    }
    
    public function editAdmin(Request $request): View
    {
        return view('profile.edita', [
            'user' => $request->user(),
        ]);
    }
    
    /**
     * Update the user's profile information.
     * This function will handle updates for admin, coach, and member.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Validasi input gambar jika ada
        $request->validate([
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);
    
        // Simpan data profil yang sudah divalidasi
        $user = $request->user();
        $user->fill($request->validated());
    
        // Cek jika ada unggahan gambar
        if ($request->hasFile('profile_image')) {
            // Simpan gambar di storage public
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
    
            // Hapus gambar lama jika ada
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
    
            // Simpan path gambar ke user
            $user->profile_image = $imagePath;
        }
    
        // Cek perubahan pada email, jika ada ubah status verifikasi email
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    
        // Simpan user
        $user->save();
    
        // Redirect sesuai role
        return match ($user->role) {
            'admin' => Redirect::route('profile.admin.edit')->with('status', 'profile-updated'),
            'coach' => Redirect::route('profile.coach.edit')->with('status', 'profile-updated'),
            default => Redirect::route('profile.edit')->with('status', 'profile-updated'),
        };
    }    

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
