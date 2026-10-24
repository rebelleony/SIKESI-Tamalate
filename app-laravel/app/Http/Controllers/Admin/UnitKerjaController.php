<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitKerjaController extends Controller
{
    private const TIPE = [
        'kecamatan' => 'Kecamatan',
        'kelurahan' => 'Kelurahan',
        'seksi'     => 'Seksi',
        'subbag'    => 'Sub-bagian',
    ];

    public function index()
    {
        $units = UnitKerja::with('induk')
            ->withCount('users')
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();

        return view('admin.unit.index', ['units' => $units, 'tipe' => self::TIPE]);
    }

    public function create()
    {
        return view('admin.unit.form', [
            'unit'    => new UnitKerja(),
            'induk'   => UnitKerja::orderBy('nama')->get(),
            'tipe'    => self::TIPE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        UnitKerja::create($data);

        return redirect()->route('admin.unit.index')
            ->with('success', "Unit kerja {$data['nama']} ditambahkan.");
    }

    public function edit(UnitKerja $unit)
    {
        return view('admin.unit.form', [
            'unit'  => $unit,
            'induk' => UnitKerja::whereKeyNot($unit->id)->orderBy('nama')->get(),
            'tipe'  => self::TIPE,
        ]);
    }

    public function update(Request $request, UnitKerja $unit)
    {
        $data = $request->validate($this->rules($unit->id));
        $unit->update($data);

        return redirect()->route('admin.unit.index')
            ->with('success', "Unit kerja {$unit->nama} diperbarui.");
    }

    public function destroy(UnitKerja $unit)
    {
        abort_if($unit->users()->exists(), 400,
            'Unit kerja masih memiliki pegawai; pindahkan dulu pegawainya.');
        abort_if($unit->anak()->exists(), 400,
            'Unit kerja masih memiliki unit anak; hapus/pindahkan unit anaknya dahulu.');

        $unit->delete();

        return back()->with('success', "Unit kerja {$unit->nama} dihapus.");
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nama'     => ['required', 'string', 'max:120'],
            'tipe'     => ['required', Rule::in(array_keys(self::TIPE))],
            'induk_id' => ['nullable', 'exists:unit_kerja,id', Rule::notIn([$ignoreId])],
        ];
    }
}
