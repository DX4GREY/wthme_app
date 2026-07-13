<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tetap kompatibel dengan database yang belum menjalankan migrasi Control Center.
        // Pada skema lama, semua akun diperlakukan aktif seperti perilaku aplikasi sebelumnya.
        if (Schema::hasColumn('users', 'is_active') && $request->user() && ! $request->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan oleh administrator.');
        }

        return $next($request);
    }
}
