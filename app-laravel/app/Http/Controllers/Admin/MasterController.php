<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;

class MasterController extends Controller
{
    /** Halaman awal "Data Master" super admin. */
    public function index()
    {
        return view('admin.master', [
            'totalPegawai'   => User::count(),
            'pegawaiAktif'   => User::where('status', 'aktif')->count(),
            'totalUnit'      => UnitKerja::count(),
            'totalRole'      => Role::count(),
        ]);
    }
}
