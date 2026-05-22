<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'                 => \App\Http\Middleware\AdminMiddleware::class,
            'panitia'               => \App\Http\Middleware\PanitiaMiddleware::class,
            'peserta'               => \App\Http\Middleware\PesertaMiddleware::class,
            'force.change.password' => \App\Http\Middleware\ForceChangePassword::class,
            'bendahara'             => \App\Http\Middleware\BendaharaMiddleware::class,
            'mentor' => \App\Http\Middleware\MentorMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();