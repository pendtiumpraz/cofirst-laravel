<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RoleSelectionRequest;
use App\Support\Auth\ActiveRole;
use App\Support\Navigation\Navigation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Active role of multi-role users (session('active_role')) and the /dashboard entry point.
 */
class RoleSwitchController extends Controller
{
    /**
     * /dashboard: send the user to the dashboard of their active role.
     *
     * Users holding roles with different dashboards who have not chosen yet go to
     * the role-selection page. A user without any dashboard role is signed out with
     * an explanation (previously this looped between /dashboard and /login).
     */
    public function dashboard(Request $request): RedirectResponse
    {
        $user = $request->user();
        $role = Navigation::activeRole($user);

        if ($role === null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda belum memiliki peran. Hubungi admin Coding First agar akses Anda diatur.',
            ]);
        }

        if (! $request->session()->has('active_role') && ActiveRole::needsSelection($user)) {
            return redirect()->route('role.selection');
        }

        return redirect()->route(ActiveRole::dashboardRoute($role));
    }

    /**
     * Show the role selection page ("Masuk sebagai …").
     */
    public function showRoleSelection(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! ActiveRole::needsSelection($user)) {
            return redirect()->route('dashboard');
        }

        return view('auth.role-selection', [
            'user' => $user,
            'options' => ActiveRole::options($user),
            'active' => Navigation::activeRole($user),
        ]);
    }

    /**
     * Set the active role for the session and open its dashboard.
     */
    public function setActiveRole(RoleSelectionRequest $request): RedirectResponse
    {
        $role = $request->validated('role');

        ActiveRole::activate($request, $role);

        return redirect()
            ->intended(route(ActiveRole::dashboardRoute($role), absolute: false))
            ->with('success', 'Anda sekarang menggunakan peran '.ActiveRole::label($role).'.');
    }

    /**
     * Switch role during a session (JSON for scripts, redirect for plain forms).
     */
    public function switchRole(RoleSelectionRequest $request): JsonResponse|RedirectResponse
    {
        $role = $request->validated('role');

        ActiveRole::activate($request, $role);

        $target = route(ActiveRole::dashboardRoute($role));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'role' => $role, 'redirect' => $target]);
        }

        return redirect()->to($target)
            ->with('success', 'Anda sekarang menggunakan peran '.ActiveRole::label($role).'.');
    }
}
