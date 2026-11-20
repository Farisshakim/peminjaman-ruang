<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.Str::lower($credentials['email'])
            .'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function dashboard(Request $request)
    {
        $route = match ($request->user()->role) {
            'admin' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            'mahasiswa' => 'mahasiswa.dashboard',
            default => abort(403, 'Peran pengguna tidak dikenali.'),
        };

        return redirect()->route($route);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Kamu berhasil keluar.');
    }
}