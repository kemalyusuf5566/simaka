<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
       $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        return match ($user->peran->nama_peran) {
            'admin' =>
                redirect()->intended(route('admin.dashboard', absolute: false)),

            'guru_mapel' =>
                redirect()->intended(route('guru.dashboard', absolute: false)),

            'wali_kelas' =>
                redirect()->intended(route('wali.dashboard', absolute: false)),

            'bk' =>
                redirect()->intended(route('bk.data-bk.index', absolute: false)),

            default =>
                redirect('/'),
        };
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
