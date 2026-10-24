<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alur penanganan surat masuk sesuai SOP: catat -> paraf Sekcam ->
        // disposisi Camat -> distribusi -> arsip.
        Schema::table('surat_masuk', function (Blueprint $t) {
            $t->enum('status', ['dicatat', 'diparaf_sekcam', 'didisposisi', 'didistribusikan', 'diarsipkan'])
                ->default('dicatat')->after('disposisi');
            $t->text('tujuan_disposisi')->nullable()->after('status');
            $t->foreignId('paraf_oleh')->nullable()->after('tujuan_disposisi')->constrained('user')->nullOnDelete();
            $t->dateTime('paraf_waktu')->nullable()->after('paraf_oleh');
            $t->foreignId('disposisi_oleh')->nullable()->after('paraf_waktu')->constrained('user')->nullOnDelete();
            $t->dateTime('disposisi_waktu')->nullable()->after('disposisi_oleh');
            $t->foreignId('distribusi_oleh')->nullable()->after('disposisi_waktu')->constrained('user')->nullOnDelete();
            $t->dateTime('distribusi_waktu')->nullable()->after('distribusi_oleh');
            $t->index('status');
        });

        // Pengarsipan surat: pencatatan dokumen, kode tata naskah, dan lokasi simpan.
        Schema::create('arsip', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_masuk_id')->nullable()->constrained('surat_masuk')->nullOnDelete();
            $t->foreignId('pengajuan_id')->nullable()->constrained('pengajuan')->nullOnDelete();
            $t->enum('jenis_dokumen', ['surat_masuk', 'surat_keluar', 'dokumen_lain'])->default('surat_masuk');
            $t->string('judul', 160);
            $t->string('kode_arsip', 60);
            $t->string('kategori', 80)->nullable();
            $t->enum('status_dokumen', ['aktif', 'tidak_aktif', 'penting'])->default('aktif');
            $t->enum('lokasi', ['lemari_arsip', 'gudang_arsip', 'lurah'])->default('lemari_arsip');
            $t->string('nomor_daftar_arsip', 60)->nullable();
            $t->foreignId('ditandatangai_oleh')->nullable()->constrained('user')->nullOnDelete();
            $t->foreignId('dicatat_oleh')->constrained('user')->restrictOnDelete();
            $t->timestamps();
            $t->index('kode_arsip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip');
        Schema::table('surat_masuk', function (Blueprint $t) {
            $t->dropConstrainedForeignId('paraf_oleh');
            $t->dropConstrainedForeignId('disposisi_oleh');
            $t->dropConstrainedForeignId('distribusi_oleh');
            $t->dropColumn([
                'status', 'tujuan_disposisi', 'paraf_waktu',
                'disposisi_waktu', 'distribusi_waktu',
            ]);
        });
    }
};
