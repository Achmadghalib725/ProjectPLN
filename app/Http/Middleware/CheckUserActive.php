<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * Cek apakah user yang sedang login masih aktif.
     * Jika tidak aktif, logout otomatis dan redirect ke halaman login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Ambil data user terbaru dari database
            $user = Auth::user();

            // Cek apakah user masih aktif
            if (!$user->is_active) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.');
            }
        }

        return $next($request);
    }
}
