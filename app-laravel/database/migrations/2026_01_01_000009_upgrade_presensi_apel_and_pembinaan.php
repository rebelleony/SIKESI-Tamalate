<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel konfigurasi jam sesi apel & mode testing
        Schema::create('pengaturan_apel', function (Blueprint $t) {
            $t->id();
            $t->string('pagi_mulai', 5)->default('07:00');
            $t->string('pagi_selesai', 5)->default('08:30');
            $t->string('sore_mulai', 5)->default('15:30');
            $t->string('sore_selesai', 5)->default('17:00');
            $t->boolean('mode_testing')->default(true);
            $t->unsignedSmallInteger('ambang_batas_tk')->default(3);
            $t->timestamps();
        });

        DB::table('pengaturan_apel')->insert([
            'pagi_mulai'      => '07:00',
            'pagi_selesai'    => '08:30',
            'sore_mulai'      => '15:30',
            'sore_selesai'    => '17:00',
            'mode_testing'    => true,
            'ambang_batas_tk' => 3,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 2. Upgrade tabel presensi_apel (SQLite-safe rebuild)
        DB::statement('PRAGMA foreign_keys = OFF;');

        Schema::create('presensi_apel_new', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pegawai_id')->constrained('user')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('user')->cascadeOnDelete();
            $t->date('tanggal');
            $t->enum('sesi', ['pagi', 'sore'])->default('pagi');
            $t->enum('status_kehadiran', ['hadir', 'izin', 'dinas_luar', 'tanpa_keterangan'])->default('hadir');
            $t->time('jam')->nullable();
            $t->dateTime('waktu_absen')->nullable();
            $t->string('foto_path')->nullable();
            $t->string('foto_apel')->nullable();
            $t->enum('status_validasi', [
                'pending', 'valid', 'ditolak',
                // legacy backward compatibility
                'menunggu', 'hadir', 'izin', 'sakit', 'alpa',
            ])->default('pending');
            $t->foreignId('validasi_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('waktu_validasi')->nullable();
            $t->string('keterangan')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->unique(['pegawai_id', 'tanggal', 'sesi']);
        });

        if (Schema::hasTable('presensi_apel')) {
            DB::statement("
                INSERT INTO presensi_apel_new (
                    id, pegawai_id, user_id, tanggal, sesi, status_kehadiran,
                    jam, waktu_absen, foto_path, foto_apel,
                    status_validasi, validasi_oleh, waktu_validasi, keterangan, created_at
                )
                SELECT
                    id, pegawai_id, pegawai_id, tanggal, sesi,
                    CASE
                        WHEN status_validasi IN ('izin') THEN 'izin'
                        WHEN status_validasi IN ('alpa', 'ditolak') THEN 'tanpa_keterangan'
                        ELSE 'hadir'
                    END,
                    jam, created_at, foto_path, foto_path,
                    status_validasi, validasi_oleh, waktu_validasi, keterangan, created_at
                FROM presensi_apel
            ");
            Schema::drop('presensi_apel');
        }

        DB::statement('ALTER TABLE presensi_apel_new RENAME TO presensi_apel;');
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_apel');
    }
};

