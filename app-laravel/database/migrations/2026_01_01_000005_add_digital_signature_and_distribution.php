<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');

        Schema::create('pengajuan_temp', function (Blueprint $t) {
            $t->id();
            $t->string('nomor_ajuan')->nullable();
            $t->foreignId('pegawai_id')->constrained('user')->cascadeOnDelete();
            $t->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $t->enum('jenis_layanan', ['cuti', 'kgb', 'kenaikan_pangkat', 'pensiun']);
            $t->json('data_form');
            $t->enum('status', [
                'draf', 
                'menunggu_verifikasi', 
                'perlu_revisi',
                'menunggu_approval_tier1', 
                'ditolak_tier1',
                'menunggu_approval_final', 
                'ditolak_final',
                'disetujui_dan_ditandatangani',
                'selesai',
                'selesai_ditolak',
                // legacy backward compatibility
                'menunggu_approval_tier2',
                'menunggu_pengiriman', 
                'dikirim_bkpsdm', 
                'disetujui',
                'ditolak_tier2',
            ])->default('draf');
            $t->date('tanggal_ajuan')->nullable();
            $t->text('catatan')->nullable();
            $t->string('file_output')->nullable();
            $t->string('nomor_agenda', 60)->nullable();
            
            // Kolom Tanda Tangan Digital Pejabat (Camat/Lurah)
            $t->foreignId('tanda_tangan_pejabat_id')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('tanda_tangan_waktu')->nullable();
            $t->string('tanda_tangan_token', 64)->nullable()->unique();
            $t->json('tanda_tangan_payload')->nullable();

            // Kolom Distribusi Hasil oleh Super Admin
            $t->foreignId('distribusi_admin_id')->nullable()->constrained('user')->nullOnDelete();
            $t->dateTime('distribusi_waktu')->nullable();
            $t->text('distribusi_catatan')->nullable();

            $t->timestamps();
            $t->index('status');
        });

        if (Schema::hasTable('pengajuan')) {
            DB::statement('
                INSERT INTO pengajuan_temp (
                    id, nomor_ajuan, pegawai_id, unit_kerja_id, jenis_layanan, data_form, status,
                    tanggal_ajuan, catatan, file_output, nomor_agenda, created_at, updated_at
                )
                SELECT 
                    id, nomor_ajuan, pegawai_id, unit_kerja_id, jenis_layanan, data_form, status,
                    tanggal_ajuan, catatan, file_output, nomor_agenda, created_at, updated_at
                FROM pengajuan
            ');

            Schema::drop('pengajuan');
        }

        DB::statement('ALTER TABLE pengajuan_temp RENAME TO pengajuan;');
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
        // No down operation needed for development SQLite refactor
    }
};
