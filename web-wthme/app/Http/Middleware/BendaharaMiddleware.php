<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BendaharaMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika belum login, tendang ke login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. IZINKAN jika dia adalah 'bendahara' ATAU 'admin'
        // Kita menggunakan pengecekan string 'role' langsung agar konsisten dengan MentorMiddleware
        if (auth()->user()->role === 'bendahara' || auth()->user()->role === 'admin') {
            return $next($request);
        }

        // 3. Selain itu, tendang ke dashboard panitia dengan pesan error
        return redirect()->route('panitia.index')
            ->with('error', 'Hanya bendahara atau admin yang memiliki otoritas untuk tindakan ini.');
    }
}