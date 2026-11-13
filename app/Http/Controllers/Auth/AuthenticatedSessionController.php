<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function createAdmin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticateForRoles(User::ROLE_CUSTOMER);

        $request->session()->regenerate();

        $destination = $request->user()->isAdmin()
            ? route($request->user()->hasRole(User::ROLE_OPERATOR) ? 'admin.products.index' : 'admin.dashboard', absolute: false)
            : route($request->user()->hasVerifiedEmail() ? 'orders.index' : 'verification.notice', absolute: false);

        return redirect()->intended($destination);
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticateForRoles(...User::ADMIN_ROLES);

        $request->session()->regenerate();

        $destination = $request->user()->hasRole(User::ROLE_OPERATOR)
            ? route('admin.products.index', absolute: false)
            : route('admin.dashboard', absolute: false);

        return redirect()->intended($destination);
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
