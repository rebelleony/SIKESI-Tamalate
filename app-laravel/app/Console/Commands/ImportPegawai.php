<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportPegawai extends Command
{
    protected $signature = 'pegawai:import
        {file : Path ke berkas .xlsx data pegawai}
        {--sheet= : Nama worksheet (default: worksheet pertama)}
        {--jenis= : Paksa jenis_pegawai utk semua baris: PNS|PPPK_PENUH|PPPK_PARUH_WAKTU}
        {--password=password : Kata sandi default untuk akun baru}
        {--dry-run : Baca & petakan saja, jangan tulis ke database}';

    protected $description = 'Impor data pegawai dari Excel (.xlsx) ke tabel user (upsert berdasarkan NIP).';

    // Peta header ternormalisasi => kolom di tabel user.
    private const MAP = [
        'nama' => 'nama',
        'nip' => 'nip',
        'tempattanggallahir' => '__ttl',
        'unitkerja' => 'unit_kerja_nama',
        'golonganpangkat' => 'pangkat_gol',
        'tmtgolongan' => 'tmt_golongan',
        'eselon' => 'eselon',
        'namajabatan' => 'jabatan',
        'tmtjabatan' => 'tmt_jabatan',
        'statuspegawai' => 'status_pegawai',
        'tmtpegawai' => 'tmt_pegawai',
        'masakerjatahun' => 'masa_kerja_tahun',
        'masakerjabulan' => 'masa_kerja_bulan',
        'jeniskelamin' => 'jenis_kelamin',
        'agama' => 'agama',
        'statusperkawinan' => 'status_perkawinan',
        'pendidikanawal' => 'pendidikan_awal',
        'pendidikanakhir' => 'pendidikan_akhir',
        'noaskes' => 'no_askes',
        'nonpwp' => 'npwp',
        'noktp' => 'no_ktp',
        'alamat' => 'alamat',
        'telp' => 'no_telp',
        'notelepon' => 'no_telp',
        'hp' => 'no_hp',
        'nohp' => 'no_hp',
        'email' => 'email',
    ];

    private const DATE_FIELDS = ['tanggal_lahir', 'tmt_golongan', 'tmt_jabatan', 'tmt_pegawai', 'tmt_cpns', 'tmt_pns'];
    private const INT_FIELDS = ['masa_kerja_tahun', 'masa_kerja_bulan'];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $defaultPassword = (string) $this->option('password');

        $forceJenis = $this->option('jenis');
        $validJenis = ['PNS', 'PPPK_PENUH', 'PPPK_PARUH_WAKTU'];
        if ($forceJenis !== null && ! in_array($forceJenis, $validJenis, true)) {
            $this->error("--jenis harus salah satu dari: " . implode(', ', $validJenis));
            return self::FAILURE;
        }

        $pegawaiRole = Role::where('nama_role', 'pegawai')->first();
        if (! $pegawaiRole && ! $dryRun) {
            $this->error("Role 'pegawai' belum ada. Jalankan `php artisan db:seed` terlebih dahulu.");
            return self::FAILURE;
        }

        $this->info('Memuat berkas... (PII tidak dicetak ke layar)');
        $spreadsheet = IOFactory::load($path);
        $sheet = $this->option('sheet')
            ? $spreadsheet->getSheetByName($this->option('sheet'))
            : $spreadsheet->getSheet(0);

        if (! $sheet) {
            $this->error('Worksheet tidak ditemukan.');
            return self::FAILURE;
        }

        $rows = $sheet->toArray(null, true, false, true); // [rowNumber => [colLetter => value]]

        $headerRow = $this->findHeaderRow($rows);
        if ($headerRow === null) {
            $this->error('Baris header tidak ditemukan (mencari baris berisi "NIP" dan "Nama").');
            return self::FAILURE;
        }
        $this->line('Header terdeteksi di baris ' . $headerRow['number'] . '.');

        $colMap = $headerRow['map']; // colLetter => kolom user

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $rowNumber => $cells) {
            if ($rowNumber <= $headerRow['number']) {
                continue;
            }

            $data = [];
            foreach ($colMap as $col => $field) {
                $data[$field] = $cells[$col] ?? null;
            }

            // Pecah "Tempat Tanggal Lahir" menjadi tempat_lahir + tanggal_lahir.
            if (array_key_exists('__ttl', $data)) {
                [$tempat, $tanggal] = $this->pecahTtl($data['__ttl']);
                unset($data['__ttl']);
                $data['tempat_lahir'] = $tempat;
                if ($tanggal) {
                    $data['tanggal_lahir'] = $tanggal;
                }
            }

            $nip = trim((string) ($data['nip'] ?? ''));
            $nama = trim((string) ($data['nama'] ?? ''));
            if ($nip === '' || $nama === '') {
                if ($nama !== '' || $nip !== '') {
                    $skipped++;
                }
                continue; // baris kosong / tidak lengkap
            }

            $attrs = $this->normalize($data);

            if ($forceJenis !== null) {
                $attrs['jenis_pegawai'] = $forceJenis;
            }

            if ($dryRun) {
                $created++;
                continue;
            }

            $existing = User::where('nip', $nip)->first();

            // Cocokkan unit kerja berdasarkan nama (bila ada master-nya).
            if (! empty($attrs['unit_kerja_nama'])) {
                $unit = UnitKerja::whereRaw('LOWER(nama) = ?', [strtolower($attrs['unit_kerja_nama'])])->first();
                if ($unit) {
                    $attrs['unit_kerja_id'] = $unit->id;
                }
            }

            if ($existing) {
                $existing->fill($attrs)->save();
                $updated++;
            } else {
                $attrs['nip'] = $nip;
                $attrs['nama'] = $nama;
                $attrs['username'] = $this->uniqueUsername($nip);
                $attrs['password_hash'] = Hash::make($defaultPassword);
                $attrs['role_id'] = $pegawaiRole->id;
                $attrs['status'] = 'aktif';
                User::create($attrs);
                $created++;
            }
        }

        $this->info('Selesai.');
        if ($dryRun) {
            $this->warn("(dry-run) Baris valid terbaca: {$created}, dilewati: {$skipped}.");
        } else {
            $this->line("Dibuat: {$created} · Diperbarui: {$updated} · Dilewati: {$skipped}");
        }

        return self::SUCCESS;
    }

    // Cari baris header: baris yang memuat sel "NIP" dan "Nama".
    private function findHeaderRow(array $rows): ?array
    {
        foreach ($rows as $rowNumber => $cells) {
            $norm = array_map(fn ($v) => $this->normalizeHeader($v), $cells);
            $hasNip = in_array('nip', $norm, true);
            $hasNama = in_array('nama', $norm, true);
            if ($hasNip && $hasNama) {
                $map = [];
                foreach ($cells as $col => $val) {
                    $key = $this->normalizeHeader($val);
                    if ($key !== '' && isset(self::MAP[$key])) {
                        $map[$col] = self::MAP[$key];
                    }
                }
                return ['number' => $rowNumber, 'map' => $map];
            }
        }
        return null;
    }

    private function normalizeHeader($value): string
    {
        $s = strtolower(trim((string) $value));
        $s = str_replace(['.', ',', '-', '_', '/', '(', ')'], ' ', $s);
        $s = preg_replace('/[^a-z0-9 ]/', '', $s);
        $s = preg_replace('/\s+/', '', $s); // rapatkan tanpa spasi

        // "Alamat Rumah" -> alamat ; "E-mail"/"Email" -> email
        if (str_starts_with($s, 'alamat')) {
            return 'alamat';
        }
        if (str_starts_with($s, 'email') || $s === 'email') {
            return 'email';
        }
        return $s;
    }

    private function normalize(array $data): array
    {
        $out = [];
        foreach ($data as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($field, self::DATE_FIELDS, true)) {
                $d = $this->parseDate($value);
                if ($d) {
                    $out[$field] = $d;
                }
                continue;
            }

            if (in_array($field, self::INT_FIELDS, true)) {
                $out[$field] = (int) preg_replace('/[^0-9]/', '', (string) $value);
                continue;
            }

            if ($field === 'jenis_kelamin') {
                $out[$field] = $this->normalizeGender($value);
                continue;
            }

            $out[$field] = trim((string) $value);
        }

        // Turunkan jenis_pegawai dari status_pegawai mentah.
        $status = strtolower((string) ($out['status_pegawai'] ?? ''));
        if ($status !== '') {
            $out['jenis_pegawai'] = $this->mapJenisPegawai($status);
        }

        return $out;
    }

    private function mapJenisPegawai(string $statusLower): string
    {
        if (str_contains($statusLower, 'paruh') || preg_match('/\bpw\b/', $statusLower) || str_contains($statusLower, 'pppk pw')) {
            return 'PPPK_PARUH_WAKTU';
        }
        if (str_contains($statusLower, 'pns') || str_contains($statusLower, 'cpns')) {
            return 'PNS';
        }
        if (str_contains($statusLower, 'pppk')) {
            return 'PPPK_PENUH';
        }
        return 'PPPK_PARUH_WAKTU';
    }

    private function normalizeGender($value): ?string
    {
        $s = strtolower(trim((string) $value));
        if ($s === '' ) {
            return null;
        }
        if (str_starts_with($s, 'l') || str_contains($s, 'laki')) {
            return 'L';
        }
        if (str_starts_with($s, 'p') || str_contains($s, 'perempuan') || str_contains($s, 'wanita')) {
            return 'P';
        }
        return null;
    }

    // "TAKALAR, 01-01-1990" -> ['TAKALAR', '1990-01-01']
    private function pecahTtl($value): array
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return [null, null];
        }
        if (preg_match('/^(.*?),\s*(.+)$/', $raw, $m)) {
            return [trim($m[1]) ?: null, $this->parseDate(trim($m[2]))];
        }
        return [$raw, null];
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Serial number Excel
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }
        $s = trim((string) $value);
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y', 'd/m/y', 'd F Y', 'd-M-Y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $s);
            if ($d !== false) {
                return $d->format('Y-m-d');
            }
        }
        try {
            return Carbon::parse($s)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function uniqueUsername(string $nip): string
    {
        $base = preg_replace('/[^a-zA-Z0-9]/', '', $nip) ?: 'pegawai';
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }
        return $username;
    }
}
