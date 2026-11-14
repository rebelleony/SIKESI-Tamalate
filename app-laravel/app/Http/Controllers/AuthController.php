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

    /**
     * Login dual-mode: NIP atau Username.
     *
     * Pegawai dapat masuk menggunakan NIP (18 digit) atau Username.
     * Sistem mencoba mencocokkan kolom `username` terlebih dahulu,
     * kemudian kolom `nip` jika tidak ditemukan via username.
     */
    public function login(Request $request)
    {
        $cred = $request->validate([
            'login'    => ['required', 'string'],   // bisa NIP atau username
            'password' => ['required', 'string'],
        ]);

        // Cari user: coba username dulu, lalu NIP
        $user = User::with('role')
            ->where('username', $cred['login'])
            ->orWhere('nip', $cred['login'])
            ->first();

        if ($user && $user->terkunci_sampai && $user->terkunci_sampai->isFuture()) {
            return back()->withErrors([
                'login' => 'Akun terkunci sementara. Coba lagi setelah ' . $user->terkunci_sampai->format('H:i') . '.',
            ])->onlyInput('login');
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
                'login' => 'NIP/Username atau kata sandi salah.',
            ])->onlyInput('login');
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
