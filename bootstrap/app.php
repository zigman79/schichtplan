<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Beibehalten, da vorhanden
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php', // Beibehalten, da vorhanden
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Globale Middleware (die meisten sind Standard in L11)
        // $middleware->trustProxies(at: '*'); // Beispiel für Proxy-Konfiguration, falls nötig

        // Web-Gruppe Middleware
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Middleware Aliase
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'teamLeader' => \App\Http\Middleware\IsTeamLeader::class,
        ]);

        // Rate Limiter Konfiguration (aus RouteServiceProvider migriert)
        \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by(fn ($request) => optional($request->user())->id ?: $request->ip());

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Benutzerdefiniertes Exception Reporting (z.B. Sentry)
        $exceptions->report(function (Throwable $e) {
            if (app()->bound('sentry') && app(Illuminate\Contracts\Debug\ExceptionHandler::class)->shouldReport($e)) {
                 app('sentry')->captureException($e);
            }
        });

        // Hier können weitere Anpassungen am Exception Handling vorgenommen werden
    })->create();
