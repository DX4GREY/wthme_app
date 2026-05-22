<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check() &&
            auth()->user()->must_change_password &&
            !$request->routeIs('password.change') &&
            !$request->routeIs('password.change.update') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('password.change')
                ->with('warning', 'Demi keamanan, silakan ganti password kamu terlebih dahulu.');
        }

        return $next($request);
    }
}