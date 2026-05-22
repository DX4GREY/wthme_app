<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PanitiaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isPanitia()) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Hanya panitia yang dapat mengakses halaman ini.');
        }
        return $next($request);
    }
}