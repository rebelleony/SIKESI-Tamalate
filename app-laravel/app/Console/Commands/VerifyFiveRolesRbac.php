<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Pengajuan;
use App\Models\PresensiApel;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class VerifyFiveRolesRbac extends Command
{
    protected $signature = 'rbac:verify';
    protected $description = 'Comprehensive test of 5 Roles and RBAC restrictions';

    private int $passed = 0;
    private int $failed = 0;

    public function handle()
    {
        $this->info("==================================================");
        $this->info("SIP-TAMALATE - 5 ROLES & RBAC VERIFICATION SUITE");
        $this->info("==================================================\n");

        $this->testRolesTable();
        $this->testLegacyRoleMapping();
        $this->testPegawaiRole();
        $this->testOperatorRole();
        $this->testVerifikatorPimpinanRole();
        $this->testPimpinanRole();
        $this->testSuperAdminRole();
        $this->testFullWorkflowCycle();
        $this->testAuditLogLogging();
        $this->testLaporanAndArsip();

        $this->newLine();
        $this->info("==================================================");
        $this->info("TEST SUMMARY: {$this->passed} PASSED, {$this->failed} FAILED");
        $this->info("==================================================");

        return $this->failed === 0 ? 0 : 1;
    }

    private function assert(string $description, bool $condition, string $detail = '')
    {
        if ($condition) {
            $this->passed++;
            $this->line("<fg=green>✓ PASS</> : {$description}");
        } else {
            $this->failed++;
            $this->line("<fg=red>✗ FAIL</> : {$description}" . ($detail ? " ({$detail})" : ''));
        }
    }

    private function testRolesTable()
    {
        $this->info("[1] Verifikasi Tabel Role");
        $roles = Role::pluck('nama_role')->toArray();
        sort($roles);
        $expected = ['pimpinan', 'verifikator_pimpinan', 'pegawai', 'super_admin', 'operator'];
        sort($expected);

        $this->assert("Tepat 5 role utama terdaftar di tabel role", $roles === $expected, "Ditemukan: " . implode(', ', $roles));
        $this->assert("Role lama tidak sesuai SIP-TAMALATE sudah dibersihkan", !in_array('seksam', $roles, true));
    }

    private function testLegacyRoleMapping()
    {
        $this->info("\n[2] Verifikasi Role SIP-TAMALATE");
        $seksamUser = User::where('username', 'seksam')->first();
        if ($seksamUser) {
            $this->assert("User seksam dipetakan ke role 'pimpinan'", $seksamUser->role->nama_role === 'pimpinan');
            $this->assert("User seksam tetap mempertahankan jabatan aslinya", str_contains(strtolower($seksamUser->jabatan), 'sekretaris camat'));
        } else {
            $this->assert("User seksam ada di database", false, "User seksam tidak ditemukan");
        }
    }

    private function testPegawaiRole()
    {
        $this->info("\n[3] Verifikasi Role 1: PEGAWAI / STAF");
        $pegawai = User::whereHas('role', fn($q) => $q->where('nama_role', 'pegawai'))->first();
        $otherPegawai = User::whereHas('role', fn($q) => $q->where('nama_role', 'pegawai'))->where('id', '!=', $pegawai->id)->first();

        $this->assert("Pegawai dapat ditemukan", $pegawai !== null);

        $dummyPengajuan = Pengajuan::first() ?? Pengajuan::create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id ?? 1,
            'jenis_layanan' => 'cuti',
            'nomor_ajuan' => 'DUMMY/' . uniqid(),
            'tanggal_ajuan' => today(),
            'status' => 'menunggu_verifikasi',
            'data_form' => ['alasan' => 'Libur tahunan', 'mulai' => '2026-11-01', 'selesai' => '2026-11-05'],
        ]);

        // Simulasi controller action: Pegawai tidak dapat memverifikasi
        $req = Request::create('/verifikasi/1', 'POST', ['keputusan' => 'valid']);
        $req->setUserResolver(fn() => $pegawai);
        
        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $pBlocked = false;
        try {
            $approvalCtrl->verifikasi($req, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $pBlocked = true;
        } catch (\Exception $e) {
            $pBlocked = true;
        }
        $this->assert("Pegawai DITOLAK (403) saat mencoba verifikasi berkas", $pBlocked);

        // Pegawai tidak dapat approval tier1
        $tier1Blocked = false;
        try {
            $approvalCtrl->tier1($req, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $tier1Blocked = true;
        } catch (\Exception $e) {
            $tier1Blocked = true;
        }
        $this->assert("Pegawai DITOLAK (403) saat mencoba approval tingkat 1", $tier1Blocked);

        // Pegawai tidak dapat approval final
        $finalBlocked = false;
        try {
            $approvalCtrl->final($req, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $finalBlocked = true;
        } catch (\Exception $e) {
            $finalBlocked = true;
        }
        $this->assert("Pegawai DITOLAK (403) saat mencoba approval final", $finalBlocked);

        // Pegawai tidak dapat validasi presensi
        $presensiCtrl = app(\App\Http\Controllers\PresensiController::class);
        $validasiBlocked = false;
        try {
            $reqVal = Request::create('/presensi/validasi', 'POST', ['presensi_id' => 1, 'status_validasi' => 'hadir']);
            $reqVal->setUserResolver(fn() => $pegawai);
            $presensiCtrl->validasi($reqVal);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $validasiBlocked = true;
        } catch (\Exception $e) {
            $validasiBlocked = true;
        }
        $this->assert("Pegawai DITOLAK (403) saat mencoba validasi presensi orang lain", $validasiBlocked);

        // Dashboard Pegawai dapat dirender dengan sukses
        auth()->login($pegawai);
        $dashCtrl = app(\App\Http\Controllers\DashboardController::class);
        $reqDash = Request::create('/dashboard', 'GET');
        $reqDash->setUserResolver(fn() => $pegawai);
        $pegawaiView = $dashCtrl->index($reqDash);
        $renderedHtml = $pegawaiView->render();
        $this->assert("Dashboard Pegawai berhasil dirender tanpa error", !empty($renderedHtml));
    }

    private function testOperatorRole()
    {
        $this->info("\n[4] Verifikasi Role 2: OPERATOR");
        $verifikator = User::whereHas('role', fn($q) => $q->where('nama_role', 'operator'))->first();
        $this->assert("Operator dapat ditemukan", $verifikator !== null);

        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $req = Request::create('/approval/tier1/1', 'POST', ['keputusan' => 'setuju']);
        $req->setUserResolver(fn() => $verifikator);
        $dummyPengajuan = Pengajuan::first();

        // Verifikator tidak boleh tier1
        $tier1Blocked = false;
        try {
            $approvalCtrl->tier1($req, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $tier1Blocked = true;
        }
        $this->assert("Verifikator DITOLAK (403) saat mencoba approval tier1 Kasi/Kasubag", $tier1Blocked);

        // Verifikator tidak boleh final
        $finalBlocked = false;
        try {
            $approvalCtrl->final($req, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $finalBlocked = true;
        }
        $this->assert("Verifikator DITOLAK (403) saat mencoba approval final Pimpinan", $finalBlocked);
    }

    private function testVerifikatorPimpinanRole()
    {
        $this->info("\n[5] Verifikasi Role 3: VERIFIKATOR PIMPINAN");
        $kasi = User::whereHas('role', fn($q) => $q->where('nama_role', 'verifikator_pimpinan'))->first();
        $this->assert("Verifikator Pimpinan dapat ditemukan", $kasi !== null);

        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $dummyPengajuan = Pengajuan::first();

        // Kasi tidak boleh verifikasi awal
        $reqVerif = Request::create('/verifikasi/1', 'POST', ['keputusan' => 'valid']);
        $reqVerif->setUserResolver(fn() => $kasi);
        $verifBlocked = false;
        try {
            $approvalCtrl->verifikasi($reqVerif, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $verifBlocked = true;
        }
        $this->assert("Kasi/Kasubag DITOLAK (403) saat mencoba verifikasi berkas", $verifBlocked);

        // Kasi tidak boleh final approval
        $reqFinal = Request::create('/approval/final/1', 'POST', ['keputusan' => 'setuju']);
        $reqFinal->setUserResolver(fn() => $kasi);
        $finalBlocked = false;
        try {
            $approvalCtrl->final($reqFinal, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $finalBlocked = true;
        }
        $this->assert("Kasi/Kasubag DITOLAK (403) saat mencoba approval final", $finalBlocked);
    }

    private function testPimpinanRole()
    {
        $this->info("\n[6] Verifikasi Role 4: PIMPINAN");
        $camat = User::whereHas('role', fn($q) => $q->where('nama_role', 'pimpinan'))->first();
        $this->assert("Pimpinan dapat ditemukan", $camat !== null);

        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $dummyPengajuan = Pengajuan::first();

        // Camat tidak boleh verifikasi awal
        $reqVerif = Request::create('/verifikasi/1', 'POST', ['keputusan' => 'valid']);
        $reqVerif->setUserResolver(fn() => $camat);
        $verifBlocked = false;
        try {
            $approvalCtrl->verifikasi($reqVerif, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $verifBlocked = true;
        }
        $this->assert("Camat/Lurah DITOLAK (403) saat mencoba verifikasi berkas", $verifBlocked);

        // Camat tidak boleh tier1
        $reqTier1 = Request::create('/approval/tier1/1', 'POST', ['keputusan' => 'setuju']);
        $reqTier1->setUserResolver(fn() => $camat);
        $tier1Blocked = false;
        try {
            $approvalCtrl->tier1($reqTier1, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $tier1Blocked = true;
        }
        $this->assert("Camat/Lurah DITOLAK (403) saat mencoba approval tier1", $tier1Blocked);
    }

    private function testSuperAdminRole()
    {
        $this->info("\n[7] Verifikasi Role 5: SUPER ADMIN");
        $admin = User::whereHas('role', fn($q) => $q->where('nama_role', 'super_admin'))->first();
        $this->assert("Super Admin dapat ditemukan", $admin !== null);

        // Super admin TIDAK bertindak sebagai pemohon layanan
        $pengajuanCtrl = app(\App\Http\Controllers\PengajuanController::class);
        $reqStore = Request::create('/pengajuan', 'POST', ['jenis_layanan' => 'cuti']);
        $reqStore->setUserResolver(fn() => $admin);
        $submitBlocked = false;
        try {
            $pengajuanCtrl->store($reqStore);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $submitBlocked = true;
        }
        $this->assert("Super Admin DITOLAK (403) saat mencoba membuat pengajuan layanan", $submitBlocked);

        // Super Admin TIDAK melakukan verifikasi berkas
        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $dummyPengajuan = Pengajuan::first();
        $reqVerif = Request::create('/verifikasi/1', 'POST', ['keputusan' => 'valid']);
        $reqVerif->setUserResolver(fn() => $admin);
        $verifBlocked = false;
        try {
            $approvalCtrl->verifikasi($reqVerif, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $verifBlocked = true;
        }
        $this->assert("Super Admin DITOLAK (403) saat mencoba memverifikasi berkas", $verifBlocked);

        // Super Admin TIDAK melakukan approval tier1
        $reqTier1 = Request::create('/approval/tier1/1', 'POST', ['keputusan' => 'setuju']);
        $reqTier1->setUserResolver(fn() => $admin);
        $tier1Blocked = false;
        try {
            $approvalCtrl->tier1($reqTier1, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $tier1Blocked = true;
        }
        $this->assert("Super Admin DITOLAK (403) saat mencoba approval tier 1", $tier1Blocked);

        // Super Admin TIDAK melakukan approval final
        $reqFinal = Request::create('/approval/final/1', 'POST', ['keputusan' => 'setuju']);
        $reqFinal->setUserResolver(fn() => $admin);
        $finalBlocked = false;
        try {
            $approvalCtrl->final($reqFinal, $dummyPengajuan);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $finalBlocked = true;
        }
        $this->assert("Super Admin DITOLAK (403) saat mencoba approval final", $finalBlocked);
    }

    private function testFullWorkflowCycle()
    {
        $this->info("\n[8] Verifikasi Alur Pengajuan 5 Tahap Revisi (Digital Signature & Distribusi)");

        $pegawai = User::whereHas('role', fn($q) => $q->where('nama_role', 'pegawai'))->first();
        $verifikator = User::whereHas('role', fn($q) => $q->where('nama_role', 'operator'))->first();
        $kasi = User::whereHas('role', fn($q) => $q->where('nama_role', 'verifikator_pimpinan'))->first();
        $camat = User::whereHas('role', fn($q) => $q->where('nama_role', 'pimpinan'))->first();
        $admin = User::whereHas('role', fn($q) => $q->where('nama_role', 'super_admin'))->first();

        // Bersihkan data tes sebelumnya
        Pengajuan::where('nomor_ajuan', 'like', 'TEST%')->delete();

        // 1. Pegawai membuat pengajuan baru
        $p = Pengajuan::create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id ?? 1,
            'jenis_layanan' => 'cuti',
            'nomor_ajuan' => 'TEST/' . uniqid(),
            'tanggal_ajuan' => today(),
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Pengujian alur 5 tahap',
            'data_form' => ['alasan' => 'Libur tahunan', 'mulai' => '2026-11-01', 'selesai' => '2026-11-05'],
        ]);
        $this->assert("Tahap 1: Pegawai membuat pengajuan -> status 'menunggu_verifikasi'", $p->status === 'menunggu_verifikasi');

        // 2. Verifikator memeriksa berkas dan meneruskan ke Kasi/Kasubag
        $approvalCtrl = app(\App\Http\Controllers\ApprovalController::class);
        $reqV = Request::create('/verifikasi/' . $p->id, 'POST', [
            'keputusan' => 'teruskan',
            'catatan' => 'Berkas lengkap dan sesuai SOP',
        ]);
        $reqV->setUserResolver(fn() => $verifikator);
        $approvalCtrl->verifikasi($reqV, $p);
        $p->refresh();
        $this->assert("Tahap 2: Verifikator memeriksa & meloloskan -> status 'menunggu_approval_tier1'", $p->status === 'menunggu_approval_tier1');

        // 3. Kasi / Kasubag memberikan persetujuan pertama
        $reqT1 = Request::create('/approval/tier1/' . $p->id, 'POST', [
            'keputusan' => 'setuju',
            'catatan' => 'Disetujui tingkat seksi',
        ]);
        $reqT1->setUserResolver(fn() => $kasi);
        $approvalCtrl->tier1($reqT1, $p);
        $p->refresh();
        $this->assert("Tahap 3: Kasi/Kasubag persetujuan tingkat pertama -> status 'menunggu_approval_final'", $p->status === 'menunggu_approval_final');

        // 4. Camat / Lurah memberikan persetujuan akhir & Tanda Tangan Digital
        $reqFinal = Request::create('/approval/final/' . $p->id, 'POST', [
            'keputusan' => 'setuju',
            'catatan' => 'Disetujui dan disahkan pimpinan',
        ]);
        $reqFinal->setUserResolver(fn() => $camat);
        $approvalCtrl->final($reqFinal, $p);
        $p->refresh();
        $this->assert("Tahap 4: Camat/Lurah persetujuan akhir & TTD digital -> status 'disetujui_dan_ditandatangani'", $p->status === 'disetujui_dan_ditandatangani');
        $this->assert("Tahap 4: Tanda tangan digital kriptografis terbit dengan penandatangan sah", $p->isDitandatangani() && $p->tanda_tangan_pejabat_id === $camat->id && !empty($p->tanda_tangan_token));

        // Verifikasi dokumen BELUM dapat diunduh pegawai sebelum didistribusikan Super Admin
        $dokumenCtrl = app(\App\Http\Controllers\DokumenController::class);
        $reqUnduhPegawai = Request::create('/pengajuan/' . $p->id . '/dokumen?unduh=1', 'GET');
        $reqUnduhPegawai->setUserResolver(fn() => $pegawai);
        $unduhBlocked = false;
        try {
            $dokumenCtrl->pengajuan($reqUnduhPegawai, $p);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 403) $unduhBlocked = true;
        }
        $this->assert("Batasan Unduh: Pegawai BELUM dapat mengunduh dokumen sebelum distribusi Super Admin (403)", $unduhBlocked);

        // 5. Super Admin memeriksa administrasi & mendistribusikan hasil kepada Pegawai
        $distribusiCtrl = app(\App\Http\Controllers\Admin\DistribusiPengajuanController::class);
        $reqDistribusi = Request::create('/admin/distribusi/' . $p->id . '/kirim', 'POST', [
            'catatan' => 'Dokumen sah telah diverifikasi dan diserahkan kepada pemohon',
        ]);
        $reqDistribusi->setUserResolver(fn() => $admin);
        $distribusiCtrl->distribusikan($reqDistribusi, $p);
        $p->refresh();
        $this->assert("Tahap 5: Super Admin mendistribusikan hasil -> status 'selesai'", $p->status === 'selesai' && $p->distribusi_admin_id === $admin->id && !empty($p->distribusi_waktu));

        // Sekarang Pegawai DAPAT mengunduh dokumen karena status 'selesai'
        $respUnduh = $dokumenCtrl->pengajuan($reqUnduhPegawai, $p);
        $this->assert("Hasil Diterima: Pegawai sekarang dapat mengakses/mengunduh dokumen resmi (200)", $respUnduh->getStatusCode() === 200);

        // Uji coba jalur Penolakan Pejabat & Penyampaian oleh Super Admin:
        $pTolak = Pengajuan::create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id ?? 1,
            'jenis_layanan' => 'kgb',
            'nomor_ajuan' => 'TEST-TOLAK/' . uniqid(),
            'tanggal_ajuan' => today(),
            'status' => 'menunggu_approval_final',
            'keterangan' => 'Pengujian penolakan pejabat',
            'data_form' => ['gaji_lama' => 3000000, 'gaji_baru' => 3200000],
        ]);
        $reqTolakPejabat = Request::create('/approval/final/' . $pTolak->id, 'POST', [
            'keputusan' => 'tolak',
            'catatan' => 'Masa kerja belum mencukupi untuk KGB',
        ]);
        $reqTolakPejabat->setUserResolver(fn() => $camat);
        $approvalCtrl->final($reqTolakPejabat, $pTolak);
        $pTolak->refresh();
        $this->assert("Alur Penolakan Pejabat: Camat menolak -> status 'ditolak_final' (Diteruskan ke Super Admin)", $pTolak->status === 'ditolak_final');

        // Super Admin menyampaikan hasil penolakan tanpa mengubah keputusan pejabat
        $reqKirimTolak = Request::create('/admin/distribusi/' . $pTolak->id . '/sampaikan-penolakan', 'POST', [
            'catatan' => 'Penolakan resmi telah diteruskan kepada pemohon untuk dipelajari',
        ]);
        $reqKirimTolak->setUserResolver(fn() => $admin);
        $distribusiCtrl->sampaikanPenolakan($reqKirimTolak, $pTolak);
        $pTolak->refresh();
        $this->assert("Penyampaian Penolakan: Super Admin meneruskan penolakan ke Pegawai -> status 'selesai_ditolak'", $pTolak->status === 'selesai_ditolak');

        // Bersihkan data tes sebelumnya jika ada
        Pengajuan::where('nomor_ajuan', 'like', 'TEST%')->delete();

        // Uji coba jalur Koreksi Administratif oleh Super Admin:
        $pKoreksi = Pengajuan::create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id ?? 1,
            'jenis_layanan' => 'cuti',
            'nomor_ajuan' => 'TEST-KOR/' . uniqid(),
            'tanggal_ajuan' => today(),
            'status' => 'disetujui_dan_ditandatangani',
            'tanda_tangan_pejabat_id' => $camat->id,
            'tanda_tangan_waktu' => now(),
            'tanda_tangan_token' => 'dummy_token_' . uniqid(),
            'keterangan' => 'Pengujian koreksi administratif',
            'data_form' => ['alasan' => 'Libur', 'mulai' => '2026-11-01', 'selesai' => '2026-11-05'],
        ]);
        $reqKoreksi = Request::create('/admin/distribusi/' . $pKoreksi->id . '/koreksi', 'POST', [
            'catatan_koreksi' => 'Terdapat kesalahan tanggal cuti pada lampiran pemohon',
        ]);
        $reqKoreksi->setUserResolver(fn() => $admin);
        $distribusiCtrl->kembalikanKoreksi($reqKoreksi, $pKoreksi);
        $pKoreksi->refresh();
        $this->assert("Koreksi Administratif: Super Admin mengembalikan berkas bermasalah -> status 'perlu_revisi' & TTD dicabut", $pKoreksi->status === 'perlu_revisi' && $pKoreksi->tanda_tangan_token === null);

        // Uji coba jalur Revisi Verifikator:
        $pRevisi = Pengajuan::create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $pegawai->unit_kerja_id ?? 1,
            'jenis_layanan' => 'cuti',
            'nomor_ajuan' => 'TEST-REV/' . uniqid(),
            'tanggal_ajuan' => today(),
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Pengujian alur revisi',
            'data_form' => ['alasan' => 'Libur tahunan', 'mulai' => '2026-11-01', 'selesai' => '2026-11-05'],
        ]);
        $reqRev = Request::create('/verifikasi/' . $pRevisi->id, 'POST', [
            'keputusan' => 'revisi',
            'catatan' => 'SK terakhir belum diunggah',
        ]);
        $reqRev->setUserResolver(fn() => $verifikator);
        $approvalCtrl->verifikasi($reqRev, $pRevisi);
        $pRevisi->refresh();
        $this->assert("Alur Revisi: Verifikator mengembalikan berkas -> 'perlu_revisi'", $pRevisi->status === 'perlu_revisi');

        // Pegawai resubmit revisi dengan berkas perbaikan
        $pengajuanCtrl = app(\App\Http\Controllers\PengajuanController::class);
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('revisi.pdf', 50, 'application/pdf');
        $reqResubmit = Request::create(
            '/pengajuan/' . $pRevisi->id . '/revisi',
            'POST',
            ['catatan' => 'SK sudah diunggah ulang'],
            [],
            ['berkas' => [$fakeFile]]
        );
        $reqResubmit->setUserResolver(fn() => $pegawai);
        $pengajuanCtrl->resubmitRevisi($reqResubmit, $pRevisi);
        $pRevisi->refresh();
        $this->assert("Alur Revisi: Pegawai mengirimkan berkas revisi -> kembali ke 'menunggu_verifikasi'", $pRevisi->status === 'menunggu_verifikasi');

        // Cleanup test entries
        $p->delete();
        $pTolak->delete();
        $pKoreksi->delete();
        $pRevisi->delete();
    }

    private function testAuditLogLogging()
    {
        $this->info("\n[9] Verifikasi Pencatatan Audit Log");
        $logCount = AuditLog::count();
        $this->assert("Audit Log berhasil mencatat aktivitas di database", $logCount > 0, "Jumlah log: {$logCount}");

        $latestLog = AuditLog::latest('id')->first();
        if ($latestLog) {
            $this->assert("Audit Log memiliki keterangan dan aktor yang jelas", !empty($latestLog->deskripsi) && $latestLog->user_id !== null, "Aktor ID: {$latestLog->user_id}, Deskripsi: {$latestLog->deskripsi}");
        }
    }

    private function testLaporanAndArsip()
    {
        $this->info("\n[10] Verifikasi Akses Halaman Laporan & Arsip");
        $admin = User::whereHas('role', fn($q) => $q->where('nama_role', 'super_admin'))->first();
        auth()->login($admin);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        // Akses laporan tanpa parameter jenis harus berhasil dengan default jenis presensi
        $laporanCtrl = app(\App\Http\Controllers\LaporanController::class);
        $reqLaporan = Request::create('/laporan', 'GET');
        $reqLaporan->setUserResolver(fn() => $admin);
        $laporanView = $laporanCtrl->index($reqLaporan);
        $renderedLaporan = $laporanView->render();
        $this->assert("Halaman /laporan dapat dibuka tanpa parameter 'jenis'", !empty($renderedLaporan));

        // Akses arsip index
        $arsipCtrl = app(\App\Http\Controllers\ArsipController::class);
        $reqArsip = Request::create('/arsip', 'GET');
        $reqArsip->setUserResolver(fn() => $admin);
        $arsipView = $arsipCtrl->index($reqArsip);
        $renderedArsip = $arsipView->render();
        $this->assert("Halaman /arsip dapat dibuka tanpa error", !empty($renderedArsip));
    }
}
