<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API pura, sin vistas ni login por sesión: nunca redirigir a un
        // "login" que no existe cuando falta el token.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API pura, sin vistas ni login por sesión: cualquier error (incluida
        // la falta de token) responde en JSON, sin importar el header Accept
        // del cliente — así nunca intenta redirigir a un "login" que no existe.
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })->create();
