<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $t) {
            $t->id();
            $t->string('nama_role', 50)->unique();
            $t->string('deskripsi')->nullable();
            $t->json('permissions')->nullable();
            $t->timestamps();
        });

        Schema::create('unit_kerja', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 120);
            $t->enum('tipe', ['kecamatan', 'kelurahan', 'seksi', 'subbag']);
            $t->foreignId('induk_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('user', function (Blueprint $t) {
            $t->id();
            $t->string('nip', 30)->unique();
            $t->string('nama', 120);
            $t->string('username', 60)->unique();
            $t->string('password_hash');
            $t->foreignId('role_id')->constrained('role')->restrictOnDelete();
            $t->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->foreignId('atasan_id')->nullable()->constrained('user')->nullOnDelete();
            $t->string('jabatan', 120)->nullable();
            $t->string('pangkat_gol', 30)->nullable();
            $t->enum('jenis_pegawai', ['PNS', 'PPPK_PENUH', 'PPPK_PARUH_WAKTU'])->default('PNS');
            $t->date('tanggal_lahir')->nullable();
            $t->date('tmt_cpns')->nullable();
            $t->date('tmt_pns')->nullable();
            $t->unsignedInteger('masa_kerja_bulan')->default(0);
            $t->decimal('gaji_pokok', 12, 2)->nullable();
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

        Schema::create('jenis_cuti', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 80)->unique();
            $t->unsignedInteger('max_hari')->nullable();
            $t->string('keterangan')->nullable();
        });

        Schema::create('pengajuan', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_ajuan', 50)->nullable()->unique();
            $t->foreignId('pegawai_id')->constrained('user')->cascadeOnDelete();
            $t->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->enum('jenis_layanan', ['cuti', 'kgb', 'kenaikan_pangkat', 'pensiun']);
            $t->json('data_form');
            $t->enum('status', [
                'draf', 'menunggu_verifikasi', 'perlu_revisi',
                'menunggu_approval_tier1', 'menunggu_approval_tier2', 'menunggu_approval_final',
                'menunggu_pengiriman', 'dikirim_bkpsdm',
                'disetujui', 'ditolak_tier1', 'ditolak_tier2', 'ditolak_final',
            ])->default('draf');
            $t->date('tanggal_ajuan')->nullable();
            $t->text('catatan')->nullable();
            $t->string('file_output')->nullable();
            $t->string('nomor_agenda', 60)->nullable();
            $t->timestamps();
            $t->index('status');
        });

        Schema::create('berkas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $t->string('nama_file', 160);
            $t->string('path');
            $t->string('tipe', 50)->nullable();
            $t->unsignedInteger('ukuran')->nullable();
            $t->string('kategori', 80)->nullable();
            $t->unsignedSmallInteger('versi')->default(1);
            $t->foreignId('uploaded_by')->constrained('user')->restrictOnDelete();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('riwayat_approval', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $t->foreignId('pejabat_id')->constrained('user')->restrictOnDelete();
            $t->enum('tahap', ['verifikasi', 'approval_tier1', 'approval_tier2', 'approval_final', 'pengiriman']);
            $t->enum('keputusan', ['teruskan', 'setuju', 'tolak', 'revisi', 'dikirim']);
            $t->text('catatan')->nullable();
            $t->dateTime('waktu')->useCurrent();
        });

        Schema::create('presensi_apel', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pegawai_id')->constrained('user')->cascadeOnDelete();
            $t->date('tanggal');
            $t->enum('sesi', ['pagi', 'sore'])->default('pagi');
            $t->time('jam');
            $t->string('foto_path');
            $t->enum('status_validasi', ['menunggu', 'hadir', 'izin', 'sakit', 'alpa'])->default('menunggu');
            $t->foreignId('validasi_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('waktu_validasi')->nullable();
            $t->string('keterangan')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->unique(['pegawai_id', 'tanggal', 'sesi']);
        });

        Schema::create('surat_masuk', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_surat', 60);
            $t->string('nomor_agenda', 60)->nullable()->unique();
            $t->string('klasifikasi', 60)->nullable();
            $t->date('tanggal_surat');
            $t->date('tanggal_terima');
            $t->string('asal_surat', 160)->nullable();
            $t->string('tujuan', 160)->nullable();
            $t->string('perihal');
            $t->enum('sifat', ['biasa', 'penting', 'segera', 'rahasia'])->default('biasa');
            $t->string('file_path')->nullable();
            $t->text('disposisi')->nullable();
            $t->foreignId('dicatat_oleh')->constrained('user')->restrictOnDelete();
            $t->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $t->string('aksi', 80);
            $t->string('entitas', 60)->nullable();
            $t->unsignedBigInteger('entitas_id')->nullable();
            $t->string('deskripsi')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->dateTime('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('surat_masuk');
        Schema::dropIfExists('presensi_apel');
        Schema::dropIfExists('riwayat_approval');
        Schema::dropIfExists('berkas');
        Schema::dropIfExists('pengajuan');
        Schema::dropIfExists('jenis_cuti');
        Schema::dropIfExists('user');
        Schema::dropIfExists('unit_kerja');
        Schema::dropIfExists('role');
    }
};
