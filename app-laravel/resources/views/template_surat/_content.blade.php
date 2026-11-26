<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
            Tata Naskah Dinas · Permendagri 83/2022 &amp; SE Sekda 2026
        </div>
        <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-slate-900">
            Contoh Form Surat &amp; Pusat Unduh Template
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Unduh template kosong siap edit (.DOCX / .PDF) atau isi langsung contoh formulir surat di web untuk melihat pratinjau dan mencetak hasilnya.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @auth
            @if(auth()->user()->hasRole('pegawai') && auth()->user()->berhakLayanan())
                <a href="{{ route('kepegawaian.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold text-xs shadow-sm transition">
                    Ajukan Layanan Kepegawaian →
                </a>
            @endif
        @else
            <a href="{{ route('surat-mandiri.portal') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-sm transition">
                Buka Portal Surat Mandiri →
            </a>
        @endauth
    </div>
</div>

{{-- BAGIAN 1: KATALOG TEMPLATE SIAP UNDUH --}}
<div class="mb-10">
    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3">
        1. Daftar Template Surat Resmi Siap Unduh (Kosong &amp; Siap Edit)
    </h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates as $key => $tpl)
            <div class="rounded-2xl border {{ $activeJenis === $key ? 'border-blue-500 ring-2 ring-blue-100' : 'border-blue-100' }} bg-white p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200 font-mono">
                            Kode {{ $tpl['kode_klasifikasi'] }}
                        </span>
                        <button type="button" onclick="selectTemplate('{{ $key }}', true)"
                                class="text-[11px] font-bold text-blue-700 hover:underline">
                            Buka Contoh Web ↓
                        </button>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">{{ $tpl['judul'] }}</h3>
                    <p class="text-[11px] font-mono text-slate-500 mt-1 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                        {{ $tpl['nomor_template'] }}
                    </p>
                    <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">{{ $tpl['deskripsi'] }}</p>

                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <div class="text-[11px] font-bold text-slate-700 mb-1">Berkas Persyaratan:</div>
                        <ul class="text-[11px] text-slate-600 list-disc pl-4 space-y-0.5">
                            @foreach($tpl['persyaratan'] as $syarat)
                                <li>{{ $syarat }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-100">
                    <a href="{{ route('template-surat.download', ['jenis' => $key, 'format' => 'docx']) }}"
                       class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs transition">
                        Unduh .DOCX
                    </a>
                    <a href="{{ route('template-surat.download', ['jenis' => $key, 'format' => 'pdf']) }}"
                       class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition">
                        Unduh .PDF
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- BAGIAN 2: CONTOH FORM INTERAKTIF DI WEB + LIVE PREVIEW --}}
<div id="simulatorSection" class="scroll-mt-24 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
                2. Contoh Form Surat Interaktif di Web &amp; Pratinjau Langsung
            </h2>
            <p class="text-xs text-slate-500">Pilih jenis surat, ubah isian di sebelah kiri, lalu lihat hasilnya langsung dan unduh sebagai Word (.docx) atau PDF.</p>
        </div>

        <div class="flex flex-wrap gap-1.5">
            @foreach($templates as $key => $tpl)
                <button type="button" data-tab="{{ $key }}" onclick="selectTemplate('{{ $key }}', false)"
                        class="tpl-tab px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ $activeJenis === $key ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-700 border-slate-200 hover:bg-blue-50' }}">
                    {{ $tpl['singkat'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6 items-start">
        {{-- PANEL KIRI: FORM PENGISIAN CONTOH --}}
        <form id="templateForm" method="POST" action="{{ route('template-surat.export', ['jenis' => $activeJenis, 'format' => 'pdf']) }}"
              class="lg:col-span-5 rounded-2xl border border-blue-100 bg-white p-5 shadow-sm space-y-4">
            @csrf

            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <span class="text-xs font-extrabold text-slate-800 uppercase">Data Pengisian Surat</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="fillSampleData()"
                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] border border-emerald-200">
                        Isi Data Contoh
                    </button>
                    <button type="button" onclick="resetPlaceholders()"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px]">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Identitas Umum --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" id="inp_tanggal_surat" value="{{ now('Asia/Makassar')->toDateString() }}"
                           oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_pegawai" id="inp_nama_pegawai"
                               value="{{ $userPrefill->nama ?? $sampleFilled['nama_pegawai'] }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NIP / NIPPPK</label>
                        <input type="text" name="nip" id="inp_nip"
                               value="{{ $userPrefill->nip ?? $sampleFilled['nip'] }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pangkat / Gol. Ruang</label>
                        <select name="pangkat_gol" id="inp_pangkat_gol" onchange="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            @foreach($golongan as $g)
                                <option value="{{ $g }}" @selected(($userPrefill->pangkat_gol ?? 'III/b') === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="jabatan" id="inp_jabatan"
                               value="{{ $userPrefill->jabatan ?? $sampleFilled['jabatan'] }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit Kerja / Kelurahan</label>
                    <select name="unit_kerja" id="inp_unit_kerja" onchange="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                        @foreach($units as $u)
                            @php $unitText = $u->tipe === 'kelurahan' ? "Kecamatan Tamalate / {$u->nama}" : "Kecamatan Tamalate / {$u->nama}"; @endphp
                            <option value="{{ $unitText }}" @selected(($userPrefill?->unitKerja?->nama ?? '') === $u->nama)>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Field Khusus: CUTI --}}
            <div data-form-section="cuti" class="space-y-3 pt-3 border-t border-slate-100">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Cuti</label>
                        <select name="jenis_cuti" id="inp_jenis_cuti" onchange="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            @foreach($jenisCutiList as $jc)
                                <option value="{{ $jc }}">{{ $jc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lama Cuti (Hari Kerja)</label>
                        <input type="number" name="lama_hari" id="inp_lama_hari" min="1" value="3"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="inp_tanggal_mulai" value="{{ now('Asia/Makassar')->addDays(2)->toDateString() }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="inp_tanggal_selesai" value="{{ now('Asia/Makassar')->addDays(4)->toDateString() }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengajuan Cuti</label>
                    <textarea name="alasan" id="inp_alasan" rows="2" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">{{ $sampleFilled['alasan'] }}</textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" name="no_hp" id="inp_no_hp" value="{{ $userPrefill->no_hp ?? $sampleFilled['no_hp'] }}"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sisa Hak Cuti (Catatan Kasubag)</label>
                        <input type="text" name="sisa_cuti" id="inp_sisa_cuti" value="12"
                               oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- Field Khusus: KGB --}}
            <div data-form-section="kgb" class="hidden space-y-3 pt-3 border-t border-slate-100">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Pokok Lama (Rp)</label>
                        <input type="text" name="gaji_lama" id="inp_gaji_lama" value="3.150.000" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TMT KGB Lama</label>
                        <input type="text" name="tmt_kgb_lama" id="inp_tmt_kgb_lama" value="01 November 2024" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Pokok Baru (Rp)</label>
                        <input type="text" name="gaji_baru" id="inp_gaji_baru" value="3.280.000" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TMT KGB Baru</label>
                        <input type="text" name="tmt_kgb_baru" id="inp_tmt_kgb_baru" value="01 November 2026" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Kerja (Tahun)</label>
                        <input type="number" name="masa_kerja_tahun" id="inp_masa_kerja_tahun" value="8" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Kerja (Bulan)</label>
                        <input type="number" name="masa_kerja_bulan" id="inp_masa_kerja_bulan" value="0" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- Field Khusus: SPT --}}
            <div data-form-section="spt" class="hidden space-y-3 pt-3 border-t border-slate-100">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Instansi Dasar Surat</label>
                        <input type="text" name="dasar_instansi" id="inp_dasar_instansi" value="BKPSDMD Kota Makassar" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Surat Undangan</label>
                        <input type="text" name="dasar_nomor" id="inp_dasar_nomor" value="800/412/BKPSDMD/XI/2026" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Maksud Penugasan</label>
                    <input type="text" name="maksud_tugas" id="inp_maksud_tugas" value="Mengikuti Bimbingan Teknis Sistem Informasi Kepegawaian" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Tujuan</label>
                        <input type="text" name="lokasi_tugas" id="inp_lokasi_tugas" value="Balai Kota Makassar" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pelaksanaan</label>
                        <input type="text" name="tanggal_tugas" id="inp_tanggal_tugas" value="{{ now('Asia/Makassar')->addDays(2)->locale('id')->translatedFormat('d F Y') }}" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pegawai 2</label>
                        <input type="text" name="pegawai2_nama" id="inp_pegawai2_nama" value="Siti Nurhaliza, S.AP" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NIP Pegawai 2</label>
                        <input type="text" name="pegawai2_nip" id="inp_pegawai2_nip" value="199708172022032005" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- Field Khusus: KENAIKAN PANGKAT --}}
            <div data-form-section="kenaikan_pangkat" class="hidden space-y-3 pt-3 border-t border-slate-100">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Diusulkan</label>
                        <select name="golongan_usulan" id="inp_golongan_usulan" onchange="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            @foreach($golongan as $g)
                                <option value="{{ $g }}" @selected($g === 'III/c')>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TMT Diusulkan</label>
                        <input type="text" name="tmt_usulan" id="inp_tmt_usulan" value="01 April 2027" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- Field Khusus: PENSIUN --}}
            <div data-form-section="pensiun" class="hidden space-y-3 pt-3 border-t border-slate-100">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pensiun</label>
                        <select name="jenis_pensiun" id="inp_jenis_pensiun" onchange="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            <option value="Batas Usia Pensiun (BUP)">Batas Usia Pensiun (BUP)</option>
                            <option value="Atas Permintaan Sendiri (APS)">Atas Permintaan Sendiri (APS)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TMT Pensiun</label>
                        <input type="text" name="tmt_usulan_pensiun" id="inp_tmt_usulan_pensiun" value="01 Januari 2027" oninput="renderPreview()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- Tombol Ekspor Hasil Isian --}}
            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2">
                <button type="submit" id="btnExportDocx"
                        formaction="{{ route('template-surat.export', ['jenis' => $activeJenis, 'format' => 'docx']) }}"
                        class="py-2.5 px-3 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs shadow-sm transition">
                    Unduh Isian (.DOCX)
                </button>
                <button type="submit" id="btnExportPdf"
                        formaction="{{ route('template-surat.export', ['jenis' => $activeJenis, 'format' => 'pdf']) }}"
                        class="py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                    Unduh Isian (.PDF)
                </button>
            </div>
        </form>

        {{-- PANEL KANAN: PRATINJAU SURAT RESMI REAL-TIME --}}
        <div class="lg:col-span-7 rounded-2xl border border-slate-200 bg-slate-100 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pratinjau Dokumen Tata Naskah Dinas (A4)</span>
                <span id="previewBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                    Live Preview
                </span>
            </div>

            <div id="letterPaper" class="bg-white border border-slate-300 shadow-sm rounded-lg p-6 sm:p-8 text-[12px] text-slate-900 leading-relaxed font-serif">
                {{-- Di-render otomatis oleh JS agar selalu sinkron dengan form --}}
            </div>
        </div>
    </div>
</div>

<script>
const TEMPLATE_META = @json($templates);
const EXPORT_BASE_URL = @json(url('/template-surat'));
let currentJenis = @json($activeJenis);
let usePlaceholders = false;

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatTanggalId(dateStr) {
    if (!dateStr || usePlaceholders) return dateStr;
    const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const m = parseInt(parts[1], 10) - 1;
    return parseInt(parts[2], 10) + ' ' + (bulan[m] || parts[1]) + ' ' + parts[0];
}

function getRomawi(dateStr) {
    const map = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    const parts = (dateStr || '').split('-');
    const m = parts.length === 3 ? parseInt(parts[1], 10) - 1 : new Date().getMonth();
    return map[m] || 'XI';
}

function v(id, placeholder) {
    if (usePlaceholders) return placeholder;
    const el = document.getElementById(id);
    return (el && el.value.trim() !== '') ? el.value.trim() : placeholder;
}

function selectTemplate(jenis, scroll) {
    currentJenis = jenis;

    document.querySelectorAll('.tpl-tab').forEach(function (btn) {
        const active = btn.dataset.tab === jenis;
        btn.classList.toggle('bg-blue-700', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('border-blue-700', active);
        btn.classList.toggle('bg-white', !active);
        btn.classList.toggle('text-slate-700', !active);
        btn.classList.toggle('border-slate-200', !active);
    });

    document.querySelectorAll('[data-form-section]').forEach(function (sec) {
        sec.classList.toggle('hidden', sec.dataset.formSection !== jenis);
    });

    const btnDocx = document.getElementById('btnExportDocx');
    const btnPdf = document.getElementById('btnExportPdf');
    if (btnDocx) btnDocx.setAttribute('formaction', EXPORT_BASE_URL + '/' + jenis + '/ekspor/docx');
    if (btnPdf) btnPdf.setAttribute('formaction', EXPORT_BASE_URL + '/' + jenis + '/ekspor/pdf');

    renderPreview();

    if (scroll) {
        const el = document.getElementById('simulatorSection');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function fillSampleData() {
    usePlaceholders = false;
    document.getElementById('previewBadge').textContent = 'Live Preview (Terisi)';
    renderPreview();
}

function resetPlaceholders() {
    usePlaceholders = true;
    document.getElementById('previewBadge').textContent = 'Mode Placeholder Kosong';
    renderPreview();
}

function renderPreview() {
    const meta = TEMPLATE_META[currentJenis] || TEMPLATE_META.cuti;
    const tglRaw = document.getElementById('inp_tanggal_surat')?.value || '2026-11-26';
    const tahun = usePlaceholders ? '{TAHUN}' : (tglRaw.split('-')[0] || '2026');
    const romawi = usePlaceholders ? '{BULAN_ROMAWI}' : getRomawi(tglRaw);
    const nomorSurat = usePlaceholders
        ? meta.nomor_template
        : (meta.kode_klasifikasi + ' / 0001 / KT / ' + romawi + ' / ' + tahun);

    const tglSurat = usePlaceholders ? '[Tanggal_Pengajuan]' : formatTanggalId(tglRaw);
    const nama = v('inp_nama_pegawai', '[Nama Pegawai]');
    const nip = v('inp_nip', '[NIP / NIPPPK Pegawai]');
    const gol = v('inp_pangkat_gol', '[Pangkat, Gol/Ruang]');
    const jabatan = v('inp_jabatan', '[Jabatan Pegawai]');
    const unit = v('inp_unit_kerja', 'Kecamatan Tamalate / Kelurahan [Nama Kelurahan]');

    const kopHtml = `
        <div class="text-center border-b-4 border-double border-slate-900 pb-2 mb-4">
            <div class="text-sm font-bold tracking-wide">PEMERINTAH KOTA MAKASSAR</div>
            <div class="text-base font-extrabold">KECAMATAN TAMALATE</div>
            <div class="text-[10px] text-slate-600">Jalan Danau Tanjung Bunga No. 1, Makassar 90224 · Pos-el: kecamatantamalate@makassarkota.go.id</div>
        </div>
    `;

    let bodyHtml = '';

    if (currentJenis === 'cuti') {
        const jenisCuti = usePlaceholders ? '' : (document.getElementById('inp_jenis_cuti')?.value || 'Cuti Tahunan');
        const lama = v('inp_lama_hari', '[Jumlah_Hari]');
        const mulai = usePlaceholders ? '[Tanggal_Mulai]' : formatTanggalId(document.getElementById('inp_tanggal_mulai')?.value);
        const selesai = usePlaceholders ? '[Tanggal_Selesai]' : formatTanggalId(document.getElementById('inp_tanggal_selesai')?.value);
        const alasan = v('inp_alasan', '[Jelaskan alasan cuti secara singkat dan jelas]');
        const hp = v('inp_no_hp', '[Nomor Telepon Aktif]');
        const sisa = v('inp_sisa_cuti', '.....');
        const chk = (label) => (!usePlaceholders && jenisCuti === label) ? '<strong class="text-emerald-700">[ ✓ ]</strong>' : '[ &nbsp; ]';

        bodyHtml = `
            <div class="text-center font-bold underline text-[13px]">FORMULIR PERMINTAAN CUTI PEGAWAI</div>
            <div class="text-center font-bold text-[11px] mb-3">Nomor: ${escHtml(nomorSurat)}</div>
            <div class="text-right mb-2">Makassar, ${escHtml(tglSurat)}</div>
            <div class="mb-3">Kepada Yth.<br><strong>Camat Tamalate Kota Makassar</strong><br>c.q. Kasubag Umum dan Kepegawaian<br>di - Makassar</div>
            <p class="mb-2">Saya yang bertanda tangan di bawah ini:</p>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <tr><td class="border border-slate-800 px-2 py-1 w-1/3">Nama Lengkap</td><td class="border border-slate-800 px-2 py-1">${escHtml(nama)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">NIP / NIPPPK</td><td class="border border-slate-800 px-2 py-1">${escHtml(nip)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">Pangkat / Gol. Ruang</td><td class="border border-slate-800 px-2 py-1">${escHtml(gol)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">Jabatan</td><td class="border border-slate-800 px-2 py-1">${escHtml(jabatan)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">Satuan Organisasi</td><td class="border border-slate-800 px-2 py-1">${escHtml(unit)}</td></tr>
            </table>
            <p class="mb-2">Dengan ini mengajukan permohonan cuti untuk tahun ${escHtml(tahun)} selama <strong>${escHtml(lama)}</strong> hari kerja, terhitung mulai tanggal <strong>${escHtml(mulai)}</strong> s.d. <strong>${escHtml(selesai)}</strong>:</p>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <tr>
                    <td class="border border-slate-800 px-2 py-1 w-1/2">${chk('Cuti Tahunan')} Cuti Tahunan</td>
                    <td class="border border-slate-800 px-2 py-1 w-1/2">${chk('Cuti Besar')} Cuti Besar (Masa kerja &gt; 5 th)</td>
                </tr>
                <tr>
                    <td class="border border-slate-800 px-2 py-1">${chk('Cuti Sakit')} Cuti Sakit (Surat Dokter)</td>
                    <td class="border border-slate-800 px-2 py-1">${chk('Cuti Alasan Penting')} Cuti Alasan Penting</td>
                </tr>
                <tr>
                    <td class="border border-slate-800 px-2 py-1">${chk('Cuti Melahirkan')} Cuti Melahirkan</td>
                    <td class="border border-slate-800 px-2 py-1">${chk('Cuti di Luar Tanggungan Negara (CLTN)')} CLTN</td>
                </tr>
            </table>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <tr><td class="border border-slate-800 px-2 py-1 w-1/3">Alasan Pengajuan Cuti</td><td class="border border-slate-800 px-2 py-1">${escHtml(alasan)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">Nomor Telepon / HP</td><td class="border border-slate-800 px-2 py-1">${escHtml(hp)}</td></tr>
            </table>
            <table class="w-full border border-slate-800 border-collapse mt-4">
                <tr>
                    <td class="border border-slate-800 p-2 w-1/2 align-top">
                        <strong>Catatan Kasubag Kepegawaian:</strong><br><br>
                        Sisa Hak Cuti: [ ${escHtml(sisa)} ] Hari<br>
                        Paraf Verifikasi: ..................
                    </td>
                    <td class="border border-slate-800 p-2 w-1/2 text-center">
                        Hormat saya,<br>Pemohon Cuti,<br><br><br>
                        <u><strong>(${escHtml(nama)})</strong></u><br>
                        NIP. ${escHtml(nip)}
                    </td>
                </tr>
            </table>
        `;
    } else if (currentJenis === 'kgb') {
        const gLama = v('inp_gaji_lama', '[Gaji Lama]');
        const tLama = v('inp_tmt_kgb_lama', '[TMT KGB Lama]');
        const gBaru = v('inp_gaji_baru', '[Gaji Baru]');
        const tBaru = v('inp_tmt_kgb_baru', '[TMT KGB Baru]');
        const mkTh = v('inp_masa_kerja_tahun', '[Tahun]');
        const mkBl = v('inp_masa_kerja_bulan', '[Bulan]');

        bodyHtml = `
            <div class="text-right mb-2">Makassar, ${escHtml(tglSurat)}</div>
            <div class="grid grid-cols-12 gap-2 mb-3">
                <div class="col-span-7 space-y-0.5">
                    <div>Nomor : ${escHtml(nomorSurat)}</div>
                    <div>Sifat : Biasa</div>
                    <div>Lampiran : 1 (satu) Berkas</div>
                    <div>Perihal : <strong>Usulan KGB A.n. ${escHtml(nama)}</strong></div>
                </div>
                <div class="col-span-5">
                    Kepada Yth.<br><strong>Kepala BKPSDMD Kota Makassar</strong><br>di - Makassar
                </div>
            </div>
            <p class="mb-2">Dengan hormat, bersama ini kami sampaikan usulan Kenaikan Gaji Berkala (KGB) atas nama:</p>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <tr><td class="border border-slate-800 px-2 py-1 w-2/5">1. Nama Pegawai</td><td class="border border-slate-800 px-2 py-1">${escHtml(nama)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">2. NIP / NIPPPK</td><td class="border border-slate-800 px-2 py-1">${escHtml(nip)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">3. Pangkat / Gol. Ruang</td><td class="border border-slate-800 px-2 py-1">${escHtml(gol)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">4. Jabatan</td><td class="border border-slate-800 px-2 py-1">${escHtml(jabatan)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">5. Unit Kerja</td><td class="border border-slate-800 px-2 py-1">${escHtml(unit)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">6. Gaji Pokok Lama</td><td class="border border-slate-800 px-2 py-1">Rp ${escHtml(gLama)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">7. TMT KGB Lama</td><td class="border border-slate-800 px-2 py-1">${escHtml(tLama)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">8. Gaji Pokok Baru</td><td class="border border-slate-800 px-2 py-1">Rp ${escHtml(gBaru)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">9. TMT KGB Baru</td><td class="border border-slate-800 px-2 py-1">${escHtml(tBaru)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">10. Masa Kerja Golongan</td><td class="border border-slate-800 px-2 py-1">${escHtml(mkTh)} Tahun ${escHtml(mkBl)} Bulan</td></tr>
            </table>
            <p class="mb-4">Terlampir: (1) SK Pangkat Terakhir, (2) SK KGB Terakhir, (3) SKP 2 Tahun Terakhir.</p>
            <div class="text-right ml-auto w-1/2 text-center">
                <strong>Camat Tamalate Kota Makassar,</strong><br><br><br>
                <u><strong>${usePlaceholders ? '[NAMA CAMAT TAMALATE]' : 'H. Emil Yudianto Tadjuddin, S.STP., M.Si'}</strong></u><br>
                NIP. ${usePlaceholders ? '[NIP Camat]' : '197804151998031002'}
            </div>
        `;
    } else if (currentJenis === 'spt') {
        const inst = v('inp_dasar_instansi', '[Nama Instansi Pengirim]');
        const noDasar = v('inp_dasar_nomor', '[Nomor_Surat_Undangan]');
        const maksud = v('inp_maksud_tugas', '[Maksud Penugasan Kedinasan]');
        const lokasi = v('inp_lokasi_tugas', '[Lokasi Tujuan]');
        const tglTugas = v('inp_tanggal_tugas', '[Tanggal Pelaksanaan]');
        const p2Nama = v('inp_pegawai2_nama', '[Nama Pegawai 2]');
        const p2Nip = v('inp_pegawai2_nip', '[NIP Pegawai 2]');

        bodyHtml = `
            <div class="text-center font-bold underline text-[13px]">SURAT PERINTAH TUGAS</div>
            <div class="text-center font-bold text-[11px] mb-3">NOMOR: ${escHtml(nomorSurat)}</div>
            <p class="mb-2"><strong>Dasar:</strong> 1. Perwali Makassar No. 16 Tahun 2017 tentang Tata Naskah Dinas. 2. Surat ${escHtml(inst)} Nomor: ${escHtml(noDasar)}.</p>
            <div class="text-center font-bold my-2">MEMERINTAHKAN:</div>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="border border-slate-800 px-2 py-1">No</th>
                        <th class="border border-slate-800 px-2 py-1">Nama Pegawai</th>
                        <th class="border border-slate-800 px-2 py-1">NIP / NIPPPK</th>
                        <th class="border border-slate-800 px-2 py-1">Gol.</th>
                        <th class="border border-slate-800 px-2 py-1">Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-slate-800 px-2 py-1 text-center">1</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(nama)}</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(nip)}</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(gol)}</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(jabatan)}</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-800 px-2 py-1 text-center">2</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(p2Nama)}</td>
                        <td class="border border-slate-800 px-2 py-1">${escHtml(p2Nip)}</td>
                        <td class="border border-slate-800 px-2 py-1">III/a</td>
                        <td class="border border-slate-800 px-2 py-1">Pengadministrasi Kepegawaian</td>
                    </tr>
                </tbody>
            </table>
            <p class="mb-4"><strong>Untuk:</strong><br>1. Melaksanakan ${escHtml(maksud)} di ${escHtml(lokasi)} pada tanggal ${escHtml(tglTugas)}.<br>2. Melaporkan hasil penugasan kepada Camat Tamalate.<br>3. Melaksanakan perintah ini dengan penuh rasa tanggung jawab.</p>
            <div class="ml-auto w-1/2 text-center">
                Ditetapkan di Makassar pada tanggal ${escHtml(tglSurat)}<br>
                <strong>Camat Tamalate Kota Makassar,</strong><br><br><br>
                <u><strong>${usePlaceholders ? '[NAMA CAMAT TAMALATE]' : 'H. Emil Yudianto Tadjuddin, S.STP., M.Si'}</strong></u>
            </div>
        `;
    } else {
        const extraLabel = currentJenis === 'kenaikan_pangkat' ? '6. Golongan Diusulkan' : '6. Jenis Pensiun';
        const extraVal = currentJenis === 'kenaikan_pangkat' ? v('inp_golongan_usulan', '[Golongan Diusulkan]') : v('inp_jenis_pensiun', '[BUP / APS]');
        const tmtLabel = currentJenis === 'kenaikan_pangkat' ? '7. TMT Pangkat Diusulkan' : '7. TMT Pensiun';
        const tmtVal = currentJenis === 'kenaikan_pangkat' ? v('inp_tmt_usulan', '[TMT Diusulkan]') : v('inp_tmt_usulan_pensiun', '[TMT Pensiun]');

        bodyHtml = `
            <div class="text-right mb-2">Makassar, ${escHtml(tglSurat)}</div>
            <div class="grid grid-cols-12 gap-2 mb-3">
                <div class="col-span-7 space-y-0.5">
                    <div>Nomor : ${escHtml(nomorSurat)}</div>
                    <div>Sifat : Biasa</div>
                    <div>Lampiran : 1 (satu) Berkas</div>
                    <div>Perihal : <strong>${escHtml(meta.judul)} A.n. ${escHtml(nama)}</strong></div>
                </div>
                <div class="col-span-5">
                    Kepada Yth.<br><strong>Kepala BKPSDMD Kota Makassar</strong><br>di - Makassar
                </div>
            </div>
            <table class="w-full border border-slate-800 border-collapse mb-3">
                <tr><td class="border border-slate-800 px-2 py-1 w-2/5">1. Nama Pegawai</td><td class="border border-slate-800 px-2 py-1">${escHtml(nama)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">2. NIP</td><td class="border border-slate-800 px-2 py-1">${escHtml(nip)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">3. Pangkat / Gol. Saat Ini</td><td class="border border-slate-800 px-2 py-1">${escHtml(gol)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">4. Jabatan</td><td class="border border-slate-800 px-2 py-1">${escHtml(jabatan)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">5. Unit Kerja</td><td class="border border-slate-800 px-2 py-1">${escHtml(unit)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">${extraLabel}</td><td class="border border-slate-800 px-2 py-1">${escHtml(extraVal)}</td></tr>
                <tr><td class="border border-slate-800 px-2 py-1">${tmtLabel}</td><td class="border border-slate-800 px-2 py-1">${escHtml(tmtVal)}</td></tr>
            </table>
            <p class="mb-4">Persyaratan terlampir: ${escHtml(meta.persyaratan.join('; '))}.</p>
            <div class="ml-auto w-1/2 text-center">
                <strong>Camat Tamalate Kota Makassar,</strong><br><br><br>
                <u><strong>${usePlaceholders ? '[NAMA CAMAT TAMALATE]' : 'H. Emil Yudianto Tadjuddin, S.STP., M.Si'}</strong></u>
            </div>
        `;
    }

    document.getElementById('letterPaper').innerHTML = kopHtml + bodyHtml;
}

document.addEventListener('DOMContentLoaded', function () {
    selectTemplate(currentJenis, false);
});
</script>
