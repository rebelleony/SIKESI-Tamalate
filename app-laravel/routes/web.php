<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitKerjaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

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

    // Pengajuan layanan (pegawai)
    Route::middleware('role:pegawai')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
    });

    // Detail pengajuan & unduh berkas (pemilik + staf terkait)
    Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/berkas/{berkas}/unduh', [PengajuanController::class, 'downloadBerkas'])->name('berkas.download');

    // Cetak SK / surat izin pengajuan yang sudah selesai menjadi PDF
    Route::get('/pengajuan/{pengajuan}/dokumen', [DokumenController::class, 'pengajuan'])->name('pengajuan.dokumen');

    // Presensi apel (semua pegawai, termasuk PPPK paruh waktu)
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
    Route::post('/presensi/validasi', [PresensiController::class, 'validasi'])
        ->middleware('role:verifikator,super_admin')->name('presensi.validasi');

    // Verifikasi berkas
    Route::post('/verifikasi/{pengajuan}', [ApprovalController::class, 'verifikasi'])
        ->middleware('role:verifikator,super_admin')->name('approval.verifikasi');

    // Persetujuan tingkat pertama (Kasi/Kasubag)
    Route::post('/approval/tier1/{pengajuan}', [ApprovalController::class, 'tier1'])
        ->middleware('role:kasi_kasubag,super_admin')->name('approval.tier1');

    // Persetujuan tingkat kedua (Sekretaris Camat: verifikasi & paraf)
    Route::post('/approval/tier2/{pengajuan}', [ApprovalController::class, 'tier2'])
        ->middleware('role:seksam,super_admin')->name('approval.tier2');

    // Persetujuan akhir (Camat/Lurah)
    Route::post('/approval/final/{pengajuan}', [ApprovalController::class, 'final'])
        ->middleware('role:camat_lurah,super_admin')->name('approval.final');

    // Registrasi & pengiriman ke BKPSDMD (staf kepegawaian)
    Route::post('/approval/kirim/{pengajuan}', [ApprovalController::class, 'kirim'])
        ->middleware('role:verifikator,super_admin')->name('approval.kirim');

    // Pencatatan, distribusi, unduh berkas surat (pelaksana/staf arsip)
    // CATATAN: route create/store harus sebelum /surat-masuk/{surat} agar tidak tertelan binding.
    Route::middleware('role:verifikator,super_admin')->group(function () {
        Route::get('/surat-masuk/create', [SuratMasukController::class, 'create'])->name('surat.create');
        Route::post('/surat-masuk', [SuratMasukController::class, 'store'])->name('surat.store');
        Route::post('/surat-masuk/{surat}/distribusi', [SuratMasukController::class, 'distribusi'])->name('surat.distribusi');
        Route::get('/surat-masuk/{surat}/unduh', [SuratMasukController::class, 'unduh'])->name('surat.unduh');
    });

    // Surat masuk & arsip: lihat untuk staf + pejabat terkait
    Route::middleware('role:verifikator,seksam,camat_lurah,super_admin')->group(function () {
        Route::get('/surat-masuk', [SuratMasukController::class, 'index'])->name('surat.index');
        Route::get('/surat-masuk/{surat}', [SuratMasukController::class, 'show'])->name('surat.show');
        Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/create', [ArsipController::class, 'create'])->name('arsip.create');
        Route::post('/arsip', [ArsipController::class, 'store'])->name('arsip.store');
    });

    // Paraf lembar disposisi (Sekretaris Camat)
    Route::post('/surat-masuk/{surat}/paraf', [SuratMasukController::class, 'paraf'])
        ->middleware('role:seksam,super_admin')->name('surat.paraf');

    // Disposisi & tanda tangan lembar disposisi (Camat)
    Route::post('/surat-masuk/{surat}/disposisi', [SuratMasukController::class, 'disposisi'])
        ->middleware('role:camat_lurah,super_admin')->name('surat.disposisi');

    // Laporan & rekapitulasi (UC-10): verifikator & super admin
    Route::middleware('role:verifikator,super_admin')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
        Route::get('/laporan/ekspor', [LaporanController::class, 'ekspor'])->name('laporan.ekspor');
    });

    // Data Master & Akun (UC-03): khusus super admin
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
    });
});
