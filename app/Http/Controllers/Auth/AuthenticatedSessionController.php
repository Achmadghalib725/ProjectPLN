<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Proses Login Bawaan Breeze
        $request->authenticate();

        // 2. Tambahan: Cek Apakah User Aktif?
        // Kita akses user yang baru saja login
        if ($request->user()->is_active == false) {
            // Jika tidak aktif, logout paksa
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Kembalikan ke halaman login dengan pesan error
            return redirect('/login')->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Admin.',
            ]);
        }

        // 3. Jika Aktif, Lanjut Buat Sesi
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
