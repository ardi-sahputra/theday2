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
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\StagingBasicAuth::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Exclude Mayar webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/mayar',
            'logout',
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        });

        $middleware->alias([
            'onboarding'         => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'invitation.access'  => \App\Http\Middleware\CheckInvitationAccess::class,
            'couple'             => \App\Http\Middleware\ResolveCoupleContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Session / CSRF expired (419). Catch by status code so it works
        // regardless of the underlying exception class. Returning a redirect
        // back keeps the user on the same page with a fresh CSRF token and a
        // friendly flash — instead of Inertia showing the raw 419 error page
        // inside a modal overlay.
        $exceptions->respond(function (
            \Symfony\Component\HttpFoundation\Response $response,
            \Throwable $e,
            \Illuminate\Http\Request $request,
        ) {
            if ($response->getStatusCode() === 419) {
                $message = app()->getLocale() === 'en'
                    ? 'Your session expired. The page was refreshed — please try again.'
                    : 'Sesi kamu kedaluwarsa. Halaman dimuat ulang — silakan coba lagi.';

                return back(303)->with('error', $message);
            }

            return $response;
        });
    })->create();
