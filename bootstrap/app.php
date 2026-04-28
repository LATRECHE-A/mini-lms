<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Application bootstrap: routing, middleware aliasing, and exception
 * rendering. Errors render via the dedicated minimal `layouts.error` layout
 * - never inside the dashboard `layouts.app` - so a 403/404 page can never
 * leak the sidebar of the role the user does not have.
 */

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Authenticated user hitting a guest-only route -> their own dashboard.
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user && $user->role === 'admin') {
                return route('admin.dashboard');
            }

            return route('student.dashboard');
        });

        // Unauthenticated user hitting a protected route -> login (no intended).
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Authentication failures: send to login, do NOT store an intended URL.
        // This is the second half of the cross-role redirect fix.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });

        // Policy denials -> 403 page (rendered with minimal layout).
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage() ?: 'Forbidden.'], 403);
            }

            return response()->view('errors.403', ['exception' => $e], 403);
        });

        // Generic HTTP errors get the appropriate minimal-layout error page.
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $status);
            }
            $view = view()->exists("errors.{$status}") ? "errors.{$status}" : 'errors.500';

            return response()->view($view, ['exception' => $e], $status);
        });
    })->create();
