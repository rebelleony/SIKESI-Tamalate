<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel utama pengajuan layanan kepegawaian (Cuti, KGB, Kenaikan Pangkat, Pensiun)
        Schema::create('pengajuan_kepegawaian', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_pengajuan', 60)->unique();
            $t->foreignId('pegawai_id')->constrained('user')->cascadeOnDelete();
            $t->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->enum('asal_wilayah', ['kelurahan', 'kecamatan'])->default('kecamatan');
            $t->enum('jenis_layanan', ['cuti', 'kgb', 'kenaikan_pangkat', 'pensiun']);
            $t->string('kode_klasifikasi', 30)->nullable();
            $t->enum('status', [
                'verifikasi_operator_kelurahan',
                'approval_lurah',
                'antrean_operator_kecamatan',
                'approval_kasubag',
                'approval_sekcam',
                'approval_camat',
                'selesai',
                'diteruskan_bkpsdm',
                'perlu_revisi',
                'ditolak',
            ])->default('antrean_operator_kecamatan');
            $t->string('meja_saat_ini', 120);
            $t->json('data_layanan');
            $t->json('checklist_kelengkapan')->nullable();
            $t->string('nomor_nominatif', 80)->nullable();
            $t->date('tanggal_nominatif')->nullable();
            $t->text('catatan_terakhir')->nullable();
            $t->timestamps();

            $t->index(['jenis_layanan', 'status']);
            $t->index('asal_wilayah');
        });

        // 2. Tabel lampiran berkas persyaratan digital (PDF max 5MB)
        Schema::create('lampiran_berkas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengajuan_kepegawaian_id')->constrained('pengajuan_kepegawaian')->cascadeOnDelete();
            $t->string('nama_dokumen', 160);
            $t->string('nama_file_asli', 180);
            $t->string('path');
            $t->unsignedInteger('ukuran_kb')->nullable();
            $t->string('mime_type', 80)->nullable();
            $t->enum('status_verifikasi', ['menunggu', 'lengkap', 'tidak_lengkap'])->default('menunggu');
            $t->string('catatan_operator', 255)->nullable();
            $t->foreignId('uploaded_by')->constrained('user')->restrictOnDelete();
            $t->timestamps();
        });

        // 3. Tabel riwayat / log status pengajuan (untuk timeline visual meja pemrosesan)
        Schema::create('log_status_pengajuan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengajuan_kepegawaian_id')->constrained('pengajuan_kepegawaian')->cascadeOnDelete();
            $t->string('status_dari', 60)->nullable();
            $t->string('status_ke', 60);
            $t->string('meja', 120);
            $t->foreignId('aktor_id')->constrained('user')->restrictOnDelete();
            $t->string('aksi', 60);
            $t->text('catatan')->nullable();
            $t->dateTime('waktu');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_status_pengajuan');
        Schema::dropIfExists('lampiran_berkas');
        Schema::dropIfExists('pengajuan_kepegawaian');
    }
};

