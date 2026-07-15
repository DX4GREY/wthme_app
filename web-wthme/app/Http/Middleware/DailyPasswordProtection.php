<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DailyAbsensiPassword;
use Illuminate\Support\Facades\Auth;

class DailyPasswordProtection
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Admin bypass - tidak perlu password
        $isAdmin = $user->role === 'admin' || strtolower($user->divisi) === 'admin';

        if ($isAdmin) {
            return $next($request);
        }

        // Cek apakah panitia sudah verifikasi password hari ini
        if ($request->session()->has('absensi_password_verified')) {
            return $next($request);
        }

        // Jika belum verifikasi, redirect ke halaman verifikasi password
        return redirect()->route('panitia.absensi.password')
            ->with('error', 'Silakan masukkan password akses absensi.');
    }
}