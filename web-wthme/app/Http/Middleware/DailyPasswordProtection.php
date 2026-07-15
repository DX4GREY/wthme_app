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

        // Safety check - if no authenticated user
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Admin bypass - tidak perlu password
        $isAdmin = $user->role === 'admin' || strtoupper($user->divisi ?? '') === 'ADMIN';

        if ($isAdmin) {
            return $next($request);
        }

        // Check for session expiration (24 hours)
        $verifiedAt = $request->session()->get('absensi_password_verified_at');
        if ($verifiedAt) {
            $verifiedTime = \Carbon\Carbon::parse($verifiedAt);
            $expiresAt = $verifiedTime->addHours(24);
            
            if (now()->isAfter($expiresAt)) {
                // Session expired - clear it
                $request->session()->forget('absensi_password_verified');
                $request->session()->forget('absensi_password_verified_at');
            } else {
                return $next($request);
            }
        }

        // Jika belum verifikasi, redirect ke halaman verifikasi password
        // Simpan URL yang dituju untuk redirect setelah verifikasi
        return redirect()->route('panitia.absensi.password')
            ->with('error', 'Silakan masukkan password akses absensi.')
            ->with('intended_url', $request->fullUrl());
    }
}