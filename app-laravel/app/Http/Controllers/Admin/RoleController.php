<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('nama_role')->get();

        return view('admin.role.index', ['roles' => $roles]);
    }

    public function create()
    {
        return view('admin.role.form', ['role' => new Role()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['permissions'] = $this->parsePermissions($request->input('permissions_text'));
        Role::create($data);

        return redirect()->route('admin.role.index')
            ->with('success', "Peran {$data['nama_role']} ditambahkan.");
    }

    public function edit(Role $role)
    {
        return view('admin.role.form', ['role' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate($this->rules($role->id));
        $data['permissions'] = $this->parsePermissions($request->input('permissions_text'));
        $role->update($data);

        return redirect()->route('admin.role.index')
            ->with('success', "Peran {$role->nama_role} diperbarui.");
    }

    public function destroy(Role $role)
    {
        abort_if($role->users()->exists(), 400,
            'Peran masih dipakai oleh pegawai; pindahkan mereka ke peran lain dahulu.');

        $role->delete();

        return back()->with('success', "Peran {$role->nama_role} dihapus.");
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nama_role' => ['required', 'string', 'max:50', Rule::unique('role', 'nama_role')->ignore($ignoreId)],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** Ubah teks (pisah baris/koma) menjadi array permission. */
    private function parsePermissions(?string $text): ?array
    {
        if (blank($text)) {
            return null;
        }

        $items = preg_split('/[\r\n,]+/', $text) ?: [];
        $items = array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));

        return $items ?: null;
    }
}
