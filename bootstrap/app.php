<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsurePortalAccess;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SetLocale::class]);

        $middleware->alias([
            'portal.access' => EnsurePortalAccess::class,
            'admin.active' => EnsureAdminIsActive::class,
            'password.change' => RequirePasswordChange::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request): string => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('portal.login'));

        $middleware->redirectUsersTo(fn (Request $request): string => $request->is('admin', 'admin/*')
            ? route('admin.dashboard')
            : route('portal.home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
