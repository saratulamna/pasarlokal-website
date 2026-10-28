<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => [
                'required',
                Rule::in(['pembeli', 'umkm']),
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route($this->dashboardRoute($user))
            ->with('success', 'Registrasi berhasil. Selamat datang!');
    }

    public function login(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));

        $key = Str::transliterate(
            $email.'|'.$request->ip()
        );

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors([
                    'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
                ])
                ->onlyInput('email');
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'status' => 'aktif',
        ];

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            RateLimiter::hit($key, 60);

            return back()
                ->withErrors([
                    'email' => 'Email atau password salah, atau akun tidak aktif.',
                ])
                ->onlyInput('email');
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        return redirect()
            ->route($this->dashboardRoute($request->user()));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Kamu berhasil keluar dari akun.');
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'pembeli' => 'pembeli.dashboard',
            'umkm' => 'umkm.dashboard',

            default => abort(
                403,
                'Role akun tidak dikenali.'
            ),
        };
    }
}
