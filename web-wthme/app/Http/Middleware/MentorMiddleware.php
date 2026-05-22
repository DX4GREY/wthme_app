<?php

// app/Http/Middleware/MentorMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MentorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Jika belum login, tendang ke login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // IZINKAN jika dia adalah 'mentor' ATAU 'admin'
        if (auth()->user()->role === 'mentor' || auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Selain itu, tendang ke dashboard dengan pesan error
        return redirect()->route('dashboard')->with('error', 'Hanya mentor atau admin yang bisa mengakses halaman ini.');
    }
}
