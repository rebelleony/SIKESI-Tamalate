<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 000006 — SIP-TAMALATE Integration
 *
 * Perubahan:
 * 1. Rename 3 nama role: verifikator→operator, kasi_kasubag→verifikator_pimpinan, camat_lurah→pimpinan
 * 2. Rename enum value jenis_pegawai: PPPK_PENUH→PPPK_PENUH_WAKTU (via tabel temp, SQLite safe)
 * 3. Tambah unit kerja lengkap Kecamatan Tamalate: 11 Kelurahan, 5 Seksi, 2 Subbagian
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. RENAME ROLE NAMES (update string di tabel `role`)
        // ─────────────────────────────────────────────────────────────────────
        $renames = [
            'verifikator'  => 'operator',
            'kasi_kasubag' => 'verifikator_pimpinan',
            'camat_lurah'  => 'pimpinan',
        ];

        foreach ($renames as $lama => $baru) {
            DB::table('role')
                ->where('nama_role', $lama)
                ->update(['nama_role' => $baru, 'updated_at' => now()]);
        }

        // Update deskripsi role sesuai SIP-TAMALATE
        $deskripsi = [
            'operator'             => 'Operator: verifikasi berkas pengajuan, validasi presensi & rekap laporan',
            'verifikator_pimpinan' => 'Verifikator Pimpinan: persetujuan tingkat pertama (Kasi/Kasubag)',
            'pimpinan'             => 'Pimpinan: persetujuan akhir & pengesahan dokumen (Camat/Lurah)',
            'super_admin'          => 'Super Admin: kelola data master, akun pengguna & hak akses sistem',
            'pegawai'              => 'Pegawai / Staf: pengajuan layanan & presensi apel',
        ];

        foreach ($deskripsi as $nama => $desc) {
            DB::table('role')
                ->where('nama_role', $nama)
                ->update(['deskripsi' => $desc, 'updated_at' => now()]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. RENAME enum value PPPK_PENUH → PPPK_PENUH_WAKTU di tabel `user`
        //    SQLite tidak support ALTER COLUMN, gunakan teknik tabel temp
        // ─────────────────────────────────────────────────────────────────────
        DB::statement('PRAGMA foreign_keys = OFF;');

        // Buat tabel user_new dengan enum yang sudah diperbarui
        Schema::create('user_new', function (Blueprint $t) {
            $t->id();
            $t->string('nip', 30)->unique();
            $t->string('nama', 120);
            $t->string('username', 60)->unique();
            $t->string('password_hash');
            $t->foreignId('role_id')->constrained('role')->restrictOnDelete();
            $t->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->foreignId('atasan_id')->nullable()->constrained('user')->nullOnDelete();
            $t->string('jabatan', 120)->nullable();
            $t->date('tmt_jabatan')->nullable();
            $t->string('pangkat_gol', 30)->nullable();
            $t->date('tmt_golongan')->nullable();
            $t->string('eselon', 30)->nullable();
            // ★ Enum diperbarui: PPPK_PENUH → PPPK_PENUH_WAKTU
            $t->enum('jenis_pegawai', ['PNS', 'PPPK_PENUH_WAKTU', 'PPPK_PARUH_WAKTU'])->default('PNS');
            $t->string('status_pegawai', 30)->nullable();
            $t->date('tmt_pegawai')->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->string('tempat_lahir', 80)->nullable();
            $t->date('tmt_cpns')->nullable();
            $t->date('tmt_pns')->nullable();
            $t->unsignedInteger('masa_kerja_tahun')->default(0);
            $t->unsignedInteger('masa_kerja_bulan')->default(0);
            $t->decimal('gaji_pokok', 12, 2)->nullable();
            $t->string('unit_kerja_nama', 160)->nullable();
            $t->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $t->string('agama', 30)->nullable();
            $t->string('status_perkawinan', 20)->nullable();
            $t->string('pendidikan_awal', 60)->nullable();
            $t->string('pendidikan_akhir', 60)->nullable();
            $t->string('no_askes', 40)->nullable();
            $t->string('npwp', 30)->nullable();
            $t->string('no_ktp', 20)->nullable();
            $t->string('alamat', 255)->nullable();
            $t->string('no_telp', 25)->nullable();
            $t->string('no_hp', 20)->nullable();
            $t->string('email', 120)->nullable();
            $t->string('foto_profil')->nullable();
            $t->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $t->unsignedTinyInteger('login_gagal')->default(0);
            $t->dateTime('terkunci_sampai')->nullable();
            $t->dateTime('last_login')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        // Salin semua data, ubah nilai PPPK_PENUH → PPPK_PENUH_WAKTU
        DB::statement("
            INSERT INTO user_new (
                id, nip, nama, username, password_hash, role_id, unit_kerja_id, atasan_id,
                jabatan, tmt_jabatan, pangkat_gol, tmt_golongan, eselon,
                jenis_pegawai, status_pegawai, tmt_pegawai,
                tanggal_lahir, tempat_lahir, tmt_cpns, tmt_pns,
                masa_kerja_tahun, masa_kerja_bulan, gaji_pokok,
                unit_kerja_nama, jenis_kelamin, agama, status_perkawinan,
                pendidikan_awal, pendidikan_akhir, no_askes, npwp, no_ktp,
                alamat, no_telp, no_hp, email, foto_profil,
                status, login_gagal, terkunci_sampai, last_login,
                remember_token, created_at, updated_at
            )
            SELECT
                id, nip, nama, username, password_hash, role_id, unit_kerja_id, atasan_id,
                jabatan, tmt_jabatan, pangkat_gol, tmt_golongan, eselon,
                CASE WHEN jenis_pegawai = 'PPPK_PENUH' THEN 'PPPK_PENUH_WAKTU' ELSE jenis_pegawai END,
                status_pegawai, tmt_pegawai,
                tanggal_lahir, tempat_lahir, tmt_cpns, tmt_pns,
                masa_kerja_tahun, masa_kerja_bulan, gaji_pokok,
                unit_kerja_nama, jenis_kelamin, agama, status_perkawinan,
                pendidikan_awal, pendidikan_akhir, no_askes, npwp, no_ktp,
                alamat, no_telp, no_hp, email, foto_profil,
                status, login_gagal, terkunci_sampai, last_login,
                remember_token, created_at, updated_at
            FROM user
        ");

        Schema::drop('user');
        DB::statement('ALTER TABLE user_new RENAME TO user;');
        DB::statement('PRAGMA foreign_keys = ON;');

        // ─────────────────────────────────────────────────────────────────────
        // 3. TAMBAH UNIT KERJA LENGKAP KECAMATAN TAMALATE
        // ─────────────────────────────────────────────────────────────────────
        $kecamatanId = DB::table('unit_kerja')
            ->where('nama', 'Kecamatan Tamalate')
            ->where('tipe', 'kecamatan')
            ->value('id');

        // Buat kecamatan jika belum ada
        if (! $kecamatanId) {
            $kecamatanId = DB::table('unit_kerja')->insertGetId([
                'nama'      => 'Kecamatan Tamalate',
                'tipe'      => 'kecamatan',
                'induk_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2 Subbagian
        $subbagian = [
            'Subbag Umum & Kepegawaian',
            'Subbag Perencanaan & Keuangan',
        ];
        foreach ($subbagian as $nama) {
            $exists = DB::table('unit_kerja')
                ->where('nama', $nama)->where('tipe', 'subbag')->exists();
            if (! $exists) {
                DB::table('unit_kerja')->insert([
                    'nama'      => $nama,
                    'tipe'      => 'subbag',
                    'induk_id'  => $kecamatanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5 Seksi
        $seksi = [
            'Seksi Pemerintahan',
            'Seksi Pembangunan',
            'Seksi Pemberdayaan Masyarakat',
            'Seksi Kesejahteraan Sosial',
            'Seksi Ketentraman & Ketertiban Umum',
        ];
        foreach ($seksi as $nama) {
            $exists = DB::table('unit_kerja')
                ->where('nama', $nama)->where('tipe', 'seksi')->exists();
            if (! $exists) {
                DB::table('unit_kerja')->insert([
                    'nama'      => $nama,
                    'tipe'      => 'seksi',
                    'induk_id'  => $kecamatanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 11 Kelurahan resmi Kecamatan Tamalate, Kota Makassar
        $kelurahan = [
            'Kelurahan Barombong',
            'Kelurahan Tanjung Merdeka',
            'Kelurahan Maccini Sombala',
            'Kelurahan Parang Tambung',
            'Kelurahan Mannuruki',
            "Kelurahan Pa'baeng-baeng",
            'Kelurahan Balang Baru',
            'Kelurahan Jongaya',
            'Kelurahan Bongaya',
            'Kelurahan Tamparang Keke',
            'Kelurahan Mangasa',
        ];
        foreach ($kelurahan as $nama) {
            $exists = DB::table('unit_kerja')
                ->where('nama', $nama)->where('tipe', 'kelurahan')->exists();
            if (! $exists) {
                DB::table('unit_kerja')->insert([
                    'nama'      => $nama,
                    'tipe'      => 'kelurahan',
                    'induk_id'  => $kecamatanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Balik nama role
        $renames = [
            'operator'             => 'verifikator',
            'verifikator_pimpinan' => 'kasi_kasubag',
            'pimpinan'             => 'camat_lurah',
        ];
        foreach ($renames as $baru => $lama) {
            DB::table('role')->where('nama_role', $baru)->update(['nama_role' => $lama, 'updated_at' => now()]);
        }

        // Hapus unit kerja yang ditambahkan (hanya yang belum ada sebelumnya)
        $toDelete = array_merge(
            ['Subbag Perencanaan & Keuangan'],
            ['Seksi Pemerintahan', 'Seksi Pembangunan', 'Seksi Pemberdayaan Masyarakat',
             'Seksi Kesejahteraan Sosial', 'Seksi Ketentraman & Ketertiban Umum'],
            ['Kelurahan Barombong', 'Kelurahan Tanjung Merdeka', 'Kelurahan Maccini Sombala',
             'Kelurahan Parang Tambung', 'Kelurahan Mannuruki', "Kelurahan Pa'baeng-baeng",
             'Kelurahan Balang Baru', 'Kelurahan Jongaya', 'Kelurahan Bongaya',
             'Kelurahan Tamparang Keke', 'Kelurahan Mangasa']
        );
        DB::table('unit_kerja')->whereIn('nama', $toDelete)->delete();
    }
};

