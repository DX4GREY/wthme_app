<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PesertaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isPeserta()) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Halaman ini untuk peserta.');
        }
        return $next($request);
    }
}