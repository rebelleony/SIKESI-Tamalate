<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan 5 role standar utama ada di tabel 'role'
        $standardRoles = [
            'super_admin'  => 'Super Admin: kelola data master, akun pengguna & hak akses sistem',
            'verifikator'  => 'Verifikator: verifikasi berkas pengajuan, validasi presensi & laporan',
            'kasi_kasubag' => 'Kasi / Kasubag: persetujuan tingkat pertama',
            'camat_lurah'  => 'Camat / Lurah: persetujuan akhir & pengesahan dokumen',
            'pegawai'      => 'Pegawai / Staf: pengajuan layanan & presensi apel',
        ];

        foreach ($standardRoles as $nama => $desc) {
            $exists = DB::table('role')->where('nama_role', $nama)->first();
            if ($exists) {
                DB::table('role')->where('id', $exists->id)->update(['deskripsi' => $desc, 'updated_at' => now()]);
            } else {
                DB::table('role')->insert([
                    'nama_role' => $nama,
                    'deskripsi' => $desc,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $camatLurahId = DB::table('role')->where('nama_role', 'camat_lurah')->value('id');

        // 2. Petakan role legacy ke 5 role baru:
        //    'seksam' dipetakan ke 'camat_lurah' (pimpinan kecamatan/kelurahan)
        $seksamRoleId = DB::table('role')->where('nama_role', 'seksam')->value('id');
        if ($seksamRoleId && $camatLurahId) {
            DB::table('user')->where('role_id', $seksamRoleId)->update(['role_id' => $camatLurahId]);
        }

        // Hapus role di luar 5 kategori standar
        DB::table('role')->whereNotIn('nama_role', array_keys($standardRoles))->delete();

        // 3. Normalisasi status pengajuan yang masih memakai status tier2 lama
        if (Schema::hasTable('pengajuan')) {
            DB::table('pengajuan')->where('status', 'menunggu_approval_tier2')
                ->update(['status' => 'menunggu_approval_final']);
            DB::table('pengajuan')->where('status', 'ditolak_tier2')
                ->update(['status' => 'ditolak_tier1']);
        }
    }

    public function down(): void
    {
        // Down migration
    }
};
