<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\KepegawaianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMandiriController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\Admin\DistribusiPengajuanController;
use App\Http\Controllers\Admin\KodeKlasifikasiController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitKerjaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Halaman publik verifikasi tanda tangan digital & keaslian dokumen
Route::get('/dokumen/verifikasi/{token}', [DokumenController::class, 'verifikasi'])->name('dokumen.verifikasi');

// Portal Layanan & Tracking Surat Mandiri (tanpa harus login berulang kali)
Route::get('/layanan-surat', [SuratMandiriController::class, 'portal'])->name('surat-mandiri.portal');
Route::post('/layanan-surat', [SuratMandiriController::class, 'submitPortal'])->name('surat-mandiri.submit');
Route::get('/tracking-surat', [SuratMandiriController::class, 'tracking'])->name('surat-mandiri.tracking');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Saya (UC-02): semua aktor
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // Pengajuan layanan (pegawai) — PPPK Paruh Waktu hanya boleh index/tracking
    Route::middleware('role:pegawai')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        // Buat & kirim pengajuan diblokir untuk PPPK Paruh Waktu
        Route::middleware('pppk_paruh_waktu')->group(function () {
            Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
            Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        });
    });

    // Detail pengajuan & unduh berkas (pemilik + staf terkait)
    Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/berkas/{berkas}/unduh', [PengajuanController::class, 'downloadBerkas'])->name('berkas.download');

    // Pegawai mengunggah berkas revisi dan mengajukan kembali (PPPK Paruh Waktu diblokir)
    Route::post('/pengajuan/{pengajuan}/revisi', [PengajuanController::class, 'resubmitRevisi'])
        ->middleware(['role:pegawai', 'pppk_paruh_waktu'])->name('pengajuan.revisi');

    // Modul Layanan Kepegawaian (Cuti, KGB, Kenaikan Pangkat, Pensiun PNS) — diblokir untuk PPPK Paruh Waktu
    Route::middleware('pppk_paruh_waktu')->group(function () {
        Route::get('/kepegawaian', [KepegawaianController::class, 'index'])->name('kepegawaian.index');
        Route::middleware('role:pegawai')->group(function () {
            Route::get('/kepegawaian/create', [KepegawaianController::class, 'create'])->name('kepegawaian.create');
            Route::post('/kepegawaian', [KepegawaianController::class, 'store'])->name('kepegawaian.store');
            Route::post('/kepegawaian/{kepegawaian}/revisi', [KepegawaianController::class, 'resubmitRevisi'])->name('kepegawaian.revisi');
        });
        Route::middleware('role:operator,verifikator_pimpinan,pimpinan,super_admin')->group(function () {
            Route::get('/kepegawaian/ekspor-kgb', [KepegawaianController::class, 'eksporKgbExcel'])->name('kepegawaian.ekspor-kgb');
            Route::get('/kepegawaian/nominatif', [KepegawaianController::class, 'nominatif'])->name('kepegawaian.nominatif');
            Route::post('/kepegawaian/nominatif', [KepegawaianController::class, 'simpanNominatif'])->name('kepegawaian.nominatif.simpan');
        });
        Route::get('/kepegawaian/lampiran/{lampiran}/unduh', [KepegawaianController::class, 'downloadLampiran'])->name('kepegawaian.lampiran.unduh');
        Route::get('/kepegawaian/{kepegawaian}', [KepegawaianController::class, 'show'])->name('kepegawaian.show');
        Route::get('/kepegawaian/{kepegawaian}/form-cuti', [KepegawaianController::class, 'cetakFormCuti'])->name('kepegawaian.form-cuti');
        Route::post('/kepegawaian/{kepegawaian}/proses', [KepegawaianController::class, 'proses'])
            ->middleware('role:operator,verifikator_pimpinan,pimpinan')->name('kepegawaian.proses');
        Route::post('/kepegawaian/{kepegawaian}/checklist', [KepegawaianController::class, 'updateChecklist'])
            ->middleware('role:operator')->name('kepegawaian.checklist');
    });

    // Cetak SK / surat izin pengajuan yang sudah selesai menjadi PDF
    Route::get('/pengajuan/{pengajuan}/dokumen', [DokumenController::class, 'pengajuan'])->name('pengajuan.dokumen');

    // Presensi apel (semua pegawai termasuk PPPK Paruh Waktu — ini yang diizinkan)
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
    // Validasi presensi & pengaturan: khusus Operator / Super Admin
    Route::post('/presensi/validasi', [PresensiController::class, 'validasi'])
        ->middleware('role:operator,super_admin')->name('presensi.validasi');
    Route::post('/presensi/pengaturan', [PresensiController::class, 'updatePengaturan'])
        ->middleware('role:operator,super_admin')->name('presensi.pengaturan');
    // Rekapitulasi Bulanan & Peringatan Pembinaan
    Route::middleware('role:operator,verifikator_pimpinan,pimpinan,super_admin')->group(function () {
        Route::get('/presensi/rekap', [PresensiController::class, 'rekap'])->name('presensi.rekap');
        Route::get('/presensi/pembinaan/{pegawai}', [PresensiController::class, 'suratPembinaan'])->name('presensi.pembinaan');
    });

    // Tahap 1: Verifikasi berkas (khusus Operator — dahulu: verifikator)
    Route::post('/verifikasi/{pengajuan}', [ApprovalController::class, 'verifikasi'])
        ->middleware('role:operator')->name('approval.verifikasi');

    // Tahap 2: Persetujuan tingkat pertama (khusus Verifikator Pimpinan — dahulu: kasi_kasubag)
    Route::post('/approval/tier1/{pengajuan}', [ApprovalController::class, 'tier1'])
        ->middleware('role:verifikator_pimpinan')->name('approval.tier1');

    // Tahap 3: Persetujuan akhir & Pengesahan (khusus Pimpinan — dahulu: camat_lurah)
    Route::post('/approval/final/{pengajuan}', [ApprovalController::class, 'final'])
        ->middleware('role:pimpinan')->name('approval.final');

    // Pencatatan, distribusi, unduh berkas surat (Operator / staf arsip)
    // CATATAN: route create/store harus sebelum /surat-masuk/{surat} agar tidak tertelan binding.
    Route::middleware('role:operator')->group(function () {
        Route::get('/surat-masuk/create', [SuratMasukController::class, 'create'])->name('surat.create');
        Route::post('/surat-masuk', [SuratMasukController::class, 'store'])->name('surat.store');
        Route::post('/surat-masuk/{surat}/distribusi', [SuratMasukController::class, 'distribusi'])->name('surat.distribusi');
        Route::get('/surat-masuk/{surat}/unduh', [SuratMasukController::class, 'unduh'])->name('surat.unduh');

        Route::get('/surat-keluar/create', [SuratKeluarController::class, 'create'])->name('surat-keluar.create');
        Route::post('/surat-keluar', [SuratKeluarController::class, 'store'])->name('surat-keluar.store');
        Route::get('/surat-keluar/preview-nomor', [SuratKeluarController::class, 'previewNomor'])->name('surat-keluar.preview');
    });

    // Surat masuk, surat keluar & arsip: lihat untuk Operator + Sekcam (verifikator_pimpinan) + Camat/Lurah (pimpinan) + Super Admin
    Route::middleware('role:operator,verifikator_pimpinan,pimpinan,super_admin')->group(function () {
        Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('surat.index');
        Route::get('/surat-masuk/{surat}', [SuratMasukController::class, 'show'])->name('surat.show');
        Route::get('/surat-masuk/{surat}/lembar-disposisi', [SuratMasukController::class, 'lembarDisposisi'])->name('surat.lembar-disposisi');

        Route::get('/surat-keluar', [SuratKeluarController::class, 'index'])->name('surat-keluar.index');
        Route::get('/surat-keluar/{suratKeluar}', [SuratKeluarController::class, 'show'])->name('surat-keluar.show');
        Route::get('/surat-keluar/{suratKeluar}/unduh', [SuratKeluarController::class, 'unduh'])->name('surat-keluar.unduh');

        Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');

        // Pengelolaan Pengajuan Surat Mandiri (internal)
        Route::get('/surat-mandiri', [SuratMandiriController::class, 'index'])->name('surat-mandiri.index');
        Route::post('/surat-mandiri/{pengajuan}/status', [SuratMandiriController::class, 'updateStatus'])->name('surat-mandiri.status');
        Route::get('/surat-mandiri/{pengajuan}/unduh', [SuratMandiriController::class, 'unduh'])->name('surat-mandiri.unduh');
    });

    // Paraf lembar disposisi (Sekcam / Pimpinan)
    Route::post('/surat-masuk/{surat}/paraf', [SuratMasukController::class, 'paraf'])
        ->middleware('role:verifikator_pimpinan,pimpinan')->name('surat.paraf');

    // Disposisi & tanda tangan lembar disposisi (Camat / Pimpinan)
    Route::post('/surat-masuk/{surat}/disposisi', [SuratMasukController::class, 'disposisi'])
        ->middleware('role:pimpinan')->name('surat.disposisi');

    // Tolak Surat Masuk (Sekcam / Camat) dengan alasan wajib
    Route::post('/surat-masuk/{surat}/tolak', [SuratMasukController::class, 'tolak'])
        ->middleware('role:verifikator_pimpinan,pimpinan')->name('surat.tolak');

    // Laporan & rekapitulasi (UC-10): Operator & Super Admin
    Route::middleware('role:operator,super_admin')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
        Route::get('/laporan/ekspor', [LaporanController::class, 'ekspor'])->name('laporan.ekspor');
    });

    // Data Master & Akun (UC-03): khusus Super Admin
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [MasterController::class, 'index'])->name('master');

        // Pegawai / akun
        Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/pegawai/create', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/pegawai/{pegawai}/edit', [PegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::patch('/pegawai/{pegawai}/status', [PegawaiController::class, 'toggleStatus'])->name('pegawai.status');
        Route::patch('/pegawai/{pegawai}/password', [PegawaiController::class, 'resetPassword'])->name('pegawai.password');

        // Unit kerja (kecamatan / kelurahan / seksi / subbag)
        Route::get('/unit-kerja', [UnitKerjaController::class, 'index'])->name('unit.index');
        Route::get('/unit-kerja/create', [UnitKerjaController::class, 'create'])->name('unit.create');
        Route::post('/unit-kerja', [UnitKerjaController::class, 'store'])->name('unit.store');
        Route::get('/unit-kerja/{unit}/edit', [UnitKerjaController::class, 'edit'])->name('unit.edit');
        Route::put('/unit-kerja/{unit}', [UnitKerjaController::class, 'update'])->name('unit.update');
        Route::delete('/unit-kerja/{unit}', [UnitKerjaController::class, 'destroy'])->name('unit.destroy');

        // Peran
        Route::get('/peran', [RoleController::class, 'index'])->name('role.index');
        Route::get('/peran/create', [RoleController::class, 'create'])->name('role.create');
        Route::post('/peran', [RoleController::class, 'store'])->name('role.store');
        Route::get('/peran/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');
        Route::put('/peran/{role}', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/peran/{role}', [RoleController::class, 'destroy'])->name('role.destroy');

        // Master Kode Klasifikasi Arsip (Permendagri No. 83 Tahun 2022)
        Route::get('/klasifikasi', [KodeKlasifikasiController::class, 'index'])->name('klasifikasi.index');
        Route::get('/klasifikasi/create', [KodeKlasifikasiController::class, 'create'])->name('klasifikasi.create');
        Route::post('/klasifikasi', [KodeKlasifikasiController::class, 'store'])->name('klasifikasi.store');
        Route::get('/klasifikasi/{klasifikasi}/edit', [KodeKlasifikasiController::class, 'edit'])->name('klasifikasi.edit');
        Route::put('/klasifikasi/{klasifikasi}', [KodeKlasifikasiController::class, 'update'])->name('klasifikasi.update');
        Route::delete('/klasifikasi/{klasifikasi}', [KodeKlasifikasiController::class, 'destroy'])->name('klasifikasi.destroy');

        // Distribusi Hasil Pengajuan & Penyampaian Penolakan
        Route::get('/distribusi', [DistribusiPengajuanController::class, 'index'])->name('distribusi.index');
        Route::get('/distribusi/{pengajuan}', [DistribusiPengajuanController::class, 'show'])->name('distribusi.show');
        Route::post('/distribusi/{pengajuan}/kirim', [DistribusiPengajuanController::class, 'distribusikan'])->name('distribusi.kirim');
        Route::post('/distribusi/{pengajuan}/sampaikan-penolakan', [DistribusiPengajuanController::class, 'sampaikanPenolakan'])->name('distribusi.sampaikan_penolakan');
        Route::post('/distribusi/{pengajuan}/koreksi', [DistribusiPengajuanController::class, 'kembalikanKoreksi'])->name('distribusi.koreksi');
    });
});
