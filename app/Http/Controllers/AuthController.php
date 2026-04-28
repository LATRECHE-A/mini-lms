<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Auth controller - login, register, logout.
 *
 * Login redirection rule: redirect strictly by role and never honour the
 * stored "intended" URL. This prevents the well-known cross-role bug where
 * an Admin's session expires on /admin/..., a Student then logs in, and
 * `redirect()->intended()` would otherwise drop the student onto the admin
 * URL - triggering a 403 inside the admin layout.
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants incorrects.']);
        }

        // Fresh session id, fresh CSRF - avoid any pre-login fixation.
        $request->session()->regenerate();

        // Drop any pre-login intended URL. We always route by role so a
        // student can never be redirected to an admin URL (and vice versa).
        $request->session()->forget('url.intended');

        return $this->redirectByRole($request->user());
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'apprenant',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(User $user)
    {
        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('student.dashboard');
    }
}
