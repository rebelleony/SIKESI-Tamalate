<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    /** Kolom profil yang boleh dikelola admin (di luar kredensial) + aturan tipenya. */
    private function profilRules(): array
    {
        return [
            'tempat_lahir'    => ['nullable', 'string', 'max:80'],
            'tanggal_lahir'   => ['nullable', 'date'],
            'jenis_kelamin'   => ['nullable', Rule::in(['L', 'P'])],
            'agama'           => ['nullable', 'string', 'max:30'],
            'status_perkawinan' => ['nullable', 'string', 'max:20'],
            'jabatan'         => ['nullable', 'string', 'max:120'],
            'tmt_jabatan'     => ['nullable', 'date'],
            'pangkat_gol'     => ['nullable', 'string', 'max:30'],
            'tmt_golongan'    => ['nullable', 'date'],
            'eselon'          => ['nullable', 'string', 'max:30'],
            'status_pegawai'  => ['nullable', 'string', 'max:30'],
            'tmt_pegawai'     => ['nullable', 'date'],
            'tmt_cpns'        => ['nullable', 'date'],
            'tmt_pns'         => ['nullable', 'date'],
            'masa_kerja_tahun' => ['nullable', 'integer', 'min:0'],
            'masa_kerja_bulan' => ['nullable', 'integer', 'min:0'],
            'gaji_pokok'      => ['nullable', 'numeric', 'min:0'],
            'pendidikan_awal' => ['nullable', 'string', 'max:60'],
            'pendidikan_akhir' => ['nullable', 'string', 'max:60'],
            'no_askes'        => ['nullable', 'string', 'max:40'],
            'npwp'            => ['nullable', 'string', 'max:30'],
            'no_ktp'          => ['nullable', 'string', 'max:20'],
            'alamat'          => ['nullable', 'string', 'max:255'],
            'no_telp'         => ['nullable', 'string', 'max:25'],
            'no_hp'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:120'],
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $role = $request->query('role');
        $status = $request->query('status');
        $unit = $request->query('unit');

        $pegawai = User::with('role', 'unitKerja', 'atasan')
            ->when($q, fn ($b) => $b->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('jabatan', 'like', "%{$q}%");
            }))
            ->when($role, fn ($b, $r) => $b->whereHas('role', fn ($x) => $x->where('nama_role', $r)))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($b) => $b->where('status', $status))
            ->when($unit, fn ($b, $u) => $b->where('unit_kerja_id', $u))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pegawai.index', [
            'pegawai' => $pegawai,
            'roles'   => Role::orderBy('nama_role')->get(),
            'units'   => UnitKerja::orderBy('nama')->get(),
            'filter'  => compact('q', 'role', 'status', 'unit'),
        ]);
    }

    public function create()
    {
        return view('admin.pegawai.form', [
            'user'    => new User(['status' => 'aktif', 'jenis_pegawai' => 'PNS']),
            'roles'   => Role::orderBy('nama_role')->get(),
            'units'   => UnitKerja::orderBy('nama')->get(),
            'atasan'  => User::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if (blank($data['username'])) {
            $data['username'] = $data['nip'];
        }
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);
        $data['status'] = $data['status'] ?? 'aktif';

        $user = User::create($data);

        return redirect()->route('admin.pegawai.index')
            ->with('success', "Pegawai {$user->nama} berhasil ditambahkan.");
    }

    public function edit(User $pegawai)
    {
        return view('admin.pegawai.form', [
            'user'    => $pegawai,
            'roles'   => Role::orderBy('nama_role')->get(),
            'units'   => UnitKerja::orderBy('nama')->get(),
            'atasan'  => User::whereKeyNot($pegawai->id)->orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan']),
        ]);
    }

    public function update(Request $request, User $pegawai)
    {
        $rules = $this->rules($pegawai->id);
        unset($rules['password']);
        $data = $request->validate($rules + [
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        // Cegah admin mengunci dirinya sendiri keluar dari hak administrator.
        $this->guardSelfDemotion($request, $pegawai, $data);

        if (blank($data['username'])) {
            $data['username'] = $data['nip'];
        }
        if (! empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password']);

        $pegawai->update($data);

        return redirect()->route('admin.pegawai.index')
            ->with('success', "Data {$pegawai->nama} diperbarui.");
    }

    /** Ubah status aktif/nonaktif tanpa menghapus riwayat (sesuai SRS alt-flow). */
    public function toggleStatus(Request $request, User $pegawai)
    {
        abort_if($pegawai->id === $request->user()->id, 400, 'Anda tidak dapat menonaktifkan akun sendiri.');

        if ($pegawai->status === 'aktif') {
            abort_if($this->superAdminAktifTerakhir($pegawai), 400,
                'Tidak dapat menonaktifkan super admin aktif terakhir.');
            $pegawai->update(['status' => 'nonaktif']);
            $pesan = "Akun {$pegawai->nama} dinonaktifkan.";
        } else {
            $pegawai->update(['status' => 'aktif']);
            $pesan = "Akun {$pegawai->nama} diaktifkan kembali.";
        }

        return back()->with('success', $pesan);
    }

    /** Reset kata sandi pegawai ke default sementara. */
    public function resetPassword(Request $request, User $pegawai)
    {
        $request->validate(['password_baru' => ['required', 'string', 'min:8']]);
        $pegawai->update(['password_hash' => Hash::make($request->password_baru)]);

        return back()->with('success', "Kata sandi {$pegawai->nama} berhasil direset.");
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nip'      => ['required', 'string', 'max:30', Rule::unique('user', 'nip')->ignore($ignoreId)],
            'nama'     => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:60', Rule::unique('user', 'username')->ignore($ignoreId)],
            'password' => ['required', 'string', 'min:8'],
            'role_id'  => ['required', 'exists:role,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerja,id'],
            'atasan_id'     => ['nullable', 'exists:user,id', Rule::notIn([$ignoreId])],
            'jenis_pegawai' => ['required', Rule::in(['PNS', 'PPPK_PENUH', 'PPPK_PARUH_WAKTU'])],
            'status'        => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            ...$this->profilRules(),
        ];
    }

    private function guardSelfDemotion(Request $request, User $pegawai, array $data): void
    {
        if ($pegawai->id !== $request->user()->id) {
            return;
        }

        $roleAdmin = Role::where('nama_role', 'super_admin')->value('id');
        $turunJabatan = $pegawai->role_id === $roleAdmin && (int) ($data['role_id'] ?? $roleAdmin) !== $roleAdmin;
        $jadiNonaktif = ($data['status'] ?? 'aktif') === 'nonaktif';

        abort_if($turunJabatan || $jadiNonaktif, 400,
            'Anda tidak dapat menurunkan peran atau menonaktifkan akun sendiri.');
    }

    private function superAdminAktifTerakhir(User $pegawai): bool
    {
        if (! $pegawai->role || $pegawai->role->nama_role !== 'super_admin') {
            return false;
        }

        return User::where('status', 'aktif')
            ->whereHas('role', fn ($q) => $q->where('nama_role', 'super_admin'))
            ->whereKeyNot($pegawai->id)
            ->doesntExist();
    }
}
