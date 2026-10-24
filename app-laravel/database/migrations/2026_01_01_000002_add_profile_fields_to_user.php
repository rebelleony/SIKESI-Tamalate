<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $t) {
            // Identitas & kepegawaian tambahan (sesuai kolom data mitra)
            $t->string('tempat_lahir', 80)->nullable()->after('tanggal_lahir');
            $t->string('unit_kerja_nama', 160)->nullable()->after('unit_kerja_id');
            $t->string('eselon', 30)->nullable()->after('pangkat_gol');
            $t->date('tmt_golongan')->nullable()->after('pangkat_gol');
            $t->date('tmt_jabatan')->nullable()->after('jabatan');
            $t->date('tmt_pegawai')->nullable()->after('jenis_pegawai');
            $t->string('status_pegawai', 30)->nullable()->after('jenis_pegawai');
            $t->unsignedInteger('masa_kerja_tahun')->default(0)->after('masa_kerja_bulan');

            // Profil pribadi
            $t->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('status');
            $t->string('agama', 30)->nullable()->after('jenis_kelamin');
            $t->string('status_perkawinan', 20)->nullable()->after('agama');
            $t->string('pendidikan_awal', 60)->nullable()->after('status_perkawinan');
            $t->string('pendidikan_akhir', 60)->nullable()->after('pendidikan_awal');

            // Kontak & nomor identitas
            $t->string('no_askes', 40)->nullable()->after('pendidikan_akhir');
            $t->string('npwp', 30)->nullable()->after('no_askes');
            $t->string('no_ktp', 20)->nullable()->after('npwp');
            $t->string('alamat', 255)->nullable()->after('no_ktp');
            $t->string('no_telp', 25)->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $t) {
            $t->dropColumn([
                'tempat_lahir', 'unit_kerja_nama', 'eselon', 'tmt_golongan', 'tmt_jabatan',
                'tmt_pegawai', 'status_pegawai', 'masa_kerja_tahun', 'jenis_kelamin', 'agama',
                'status_perkawinan', 'pendidikan_awal', 'pendidikan_akhir', 'no_askes', 'npwp',
                'no_ktp', 'alamat', 'no_telp',
            ]);
        });
    }
};
