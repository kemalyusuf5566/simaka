<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /*
        |--------------------------------------------------------------------------
        | Middleware Alias (Laravel 12)
        |--------------------------------------------------------------------------
        | Semua alias middleware didaftarkan di sini
        */

        $middleware->alias([
            'role'         => \App\Http\Middleware\RoleMiddleware::class,
            'roleaccess'   => \App\Http\Middleware\RoleAccess::class,
            'guru.context' => \App\Http\Middleware\GuruContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->with('message', 'Sesi Anda sudah berakhir. Silakan login ulang.');
            }

            return $response;
        });
    })
    ->create();
