<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Data Kode Klasifikasi Arsip (Permendagri No. 83 Tahun 2022)
        Schema::create('kode_klasifikasi', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 30)->unique();
            $t->string('nama', 180);
            $t->string('induk_kode', 30)->nullable();
            $t->text('keterangan')->nullable();
            $t->timestamps();
            $t->index('induk_kode');
        });

        // 2. Perbarui tabel surat_masuk (SQLite-safe table rebuild agar enum status mencakup 'ditolak')
        DB::statement('PRAGMA foreign_keys = OFF;');

        Schema::create('surat_masuk_new', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('no_agenda')->nullable();
            $t->string('nomor_surat', 60);
            $t->string('nomor_agenda', 60)->nullable()->unique();
            $t->string('klasifikasi', 60)->nullable();
            $t->foreignId('kode_klasifikasi_id')->nullable()->constrained('kode_klasifikasi')->nullOnDelete();
            $t->date('tanggal_surat');
            $t->date('tanggal_terima');
            $t->string('asal_surat', 160)->nullable();
            $t->string('tujuan', 160)->nullable();
            $t->string('perihal');
            $t->text('keterangan')->nullable();
            $t->enum('sifat', ['biasa', 'penting', 'segera', 'rahasia'])->default('biasa');
            $t->string('file_path')->nullable();
            $t->text('disposisi')->nullable();
            $t->enum('status', [
                'dicatat',
                'diparaf_sekcam',
                'didisposisi',
                'didistribusikan',
                'diarsipkan',
                'ditolak',
            ])->default('dicatat');
            $t->text('tujuan_disposisi')->nullable();
            $t->foreignId('tujuan_unit_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->foreignId('paraf_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('paraf_waktu')->nullable();
            $t->string('paraf_token', 64)->nullable();
            $t->foreignId('disposisi_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('disposisi_waktu')->nullable();
            $t->string('disposisi_token', 64)->nullable();
            $t->foreignId('distribusi_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('distribusi_waktu')->nullable();
            $t->text('alasan_penolakan')->nullable();
            $t->foreignId('ditolak_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('ditolak_waktu')->nullable();
            $t->foreignId('dicatat_oleh')->constrained('user')->restrictOnDelete();
            $t->timestamps();
            $t->index('status');
        });

        if (Schema::hasTable('surat_masuk')) {
            DB::statement('
                INSERT INTO surat_masuk_new (
                    id, no_agenda, nomor_surat, nomor_agenda, klasifikasi, tanggal_surat, tanggal_terima,
                    asal_surat, tujuan, perihal, sifat, file_path, disposisi, status,
                    tujuan_disposisi, paraf_oleh, paraf_waktu, disposisi_oleh, disposisi_waktu,
                    distribusi_oleh, distribusi_waktu, dicatat_oleh, created_at, updated_at
                )
                SELECT
                    id, id, nomor_surat, nomor_agenda, klasifikasi, tanggal_surat, tanggal_terima,
                    asal_surat, tujuan, perihal, sifat, file_path, disposisi, status,
                    tujuan_disposisi, paraf_oleh, paraf_waktu, disposisi_oleh, disposisi_waktu,
                    distribusi_oleh, distribusi_waktu, dicatat_oleh, created_at, updated_at
                FROM surat_masuk
            ');
            Schema::drop('surat_masuk');
        }

        DB::statement('ALTER TABLE surat_masuk_new RENAME TO surat_masuk;');
        DB::statement('PRAGMA foreign_keys = ON;');

        // 3. Buku Agenda Surat Keluar & Generator Nomor Otomatis
        Schema::create('surat_keluar', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('no_urut');
            $t->unsignedSmallInteger('tahun');
            $t->foreignId('kode_klasifikasi_id')->constrained('kode_klasifikasi')->restrictOnDelete();
            $t->string('nomor_surat', 80)->unique();
            $t->string('instansi_tujuan', 180);
            $t->date('tgl_surat');
            $t->string('perihal', 255);
            $t->string('penanggung_jawab', 160);
            $t->text('keterangan')->nullable();
            $t->string('file_path')->nullable();
            $t->foreignId('dicatat_oleh')->constrained('user')->restrictOnDelete();
            $t->timestamps();
            $t->unique(['no_urut', 'tahun']);
            $t->index('tgl_surat');
        });

        // 4. Layanan Pengajuan & Tracking Surat Mandiri (Portal Layanan Kelurahan)
        Schema::create('pengajuan_surat_mandiri', function (Blueprint $t) {
            $t->id();
            $t->string('kode_tracking', 30)->unique();
            $t->string('jenis_surat', 80);
            $t->string('nama_pemohon', 160);
            $t->string('nip_nik', 30)->nullable();
            $t->string('unit_asal', 160);
            $t->string('no_hp', 25)->nullable();
            $t->string('perihal', 255);
            $t->text('keterangan')->nullable();
            $t->string('file_pendukung')->nullable();
            $t->enum('status', [
                'diajukan',
                'diproses_operator',
                'menunggu_pimpinan',
                'selesai',
                'ditolak',
            ])->default('diajukan');
            $t->text('catatan_admin')->nullable();
            $t->foreignId('surat_keluar_id')->nullable()->constrained('surat_keluar')->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $t->foreignId('diproses_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('waktu_diproses')->nullable();
            $t->dateTime('waktu_selesai')->nullable();
            $t->timestamps();
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surat_mandiri');
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('kode_klasifikasi');
    }
};

