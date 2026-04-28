<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Restricts a route group to a single role.
 *
 * If a logged-in user with the wrong role hits a route, we redirect to THEIR
 * own dashboard instead of 403'ing. A 403 inside a layout switch is bad UX;
 * a clean redirect avoids the "admin layout wraps a student's 403" symptom
 * entirely. A guest is handled upstream by the `auth` middleware.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === $role) {
            return $next($request);
        }

        // Wrong role - send the user to where they belong.
        $target = $user->role === 'admin'
            ? route('admin.dashboard')
            : route('student.dashboard');

        // Avoid redirect loops if for some reason the target itself is wrong.
        if ($request->is(ltrim(parse_url($target, PHP_URL_PATH), '/'))) {
            abort(403, 'Accès non autorisé.');
        }

        return redirect()->to($target);
    }
}
