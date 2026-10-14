<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Auth\ActiveRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $registrationOpen = Route::has('register')
            && filter_var(function_exists('setting') ? setting('security.registration_enabled', true) : true, FILTER_VALIDATE_BOOLEAN);

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'canRegister' => $registrationOpen,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * Users holding roles with different dashboards choose one first; everybody
     * else lands on the dashboard of their (only) role.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('active_role');

        $user = $request->user();

        if (ActiveRole::needsSelection($user)) {
            return redirect()->route('role.selection');
        }

        $role = ActiveRole::rolesOf($user)[0];
        $request->session()->put('active_role', $role);

        return redirect()->intended(route(ActiveRole::dashboardRoute($role), absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
