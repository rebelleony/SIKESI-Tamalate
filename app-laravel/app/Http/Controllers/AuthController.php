<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')->where('username', $cred['username'])->first();

        if ($user && $user->terkunci_sampai && $user->terkunci_sampai->isFuture()) {
            return back()->withErrors([
                'username' => 'Akun terkunci sementara. Coba lagi setelah ' . $user->terkunci_sampai->format('H:i') . '.',
            ])->onlyInput('username');
        }

        if (! $user || ! $user->isAktif() || ! Hash::check($cred['password'], $user->password_hash)) {
            if ($user) {
                $user->login_gagal += 1;
                if ($user->login_gagal >= 5) {
                    $user->terkunci_sampai = now()->addMinutes(15);
                    $user->login_gagal = 0;
                }
                $user->save();
            }
            return back()->withErrors([
                'username' => 'NIP/Username atau kata sandi salah.',
            ])->onlyInput('username');
        }

        $user->forceFill(['login_gagal' => 0, 'terkunci_sampai' => null, 'last_login' => now()])->save();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
