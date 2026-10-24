<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    /** Profil Saya: identitas baca-saja + form kontak & ganti sandi. */
    public function show(Request $request)
    {
        $user = $request->user();
        $user->load('role', 'unitKerja', 'atasan');

        return view('profil.show', compact('user'));
    }

    /** Perbarui field yang diizinkan: kontak, email, foto profil. */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email'   => ['nullable', 'email', 'max:120'],
            'no_telp' => ['nullable', 'string', 'max:25'],
            'no_hp'   => ['nullable', 'string', 'max:20'],
            'alamat'  => ['nullable', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('foto_profil')) {
            $path = $request->file('foto_profil')->store('profil', 'public');
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $data['foto_profil'] = $path;
        } else {
            unset($data['foto_profil']);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /** Ganti kata sandi mandiri: wajib cocokkan sandi lama. */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'kata_sandi_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'kata_sandi_lama.current_password' => 'Kata sandi lama tidak cocok.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $request->user()->update([
            'password_hash' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
