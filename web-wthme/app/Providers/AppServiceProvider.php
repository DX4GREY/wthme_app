<?php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

// Disini logika requests akan dibatasi sesuai dengan kebutuhan, misal untuk face-api, login, dan register.

public function boot(): void
{
    RateLimiter::for('face-api', function (Request $request) {
        return Limit::perMinute(60)->by($request->ip());
    });

    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    RateLimiter::for('register', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });
}
