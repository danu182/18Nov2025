<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request  $request)
    {
        if ($request->ajax()) {
            $query = Role::select(['id', 'name', 'guard_name']);

            return DataTables::of($query)
                ->addColumn('action', function ($role) {
                    // Tombol Edit dan Delete untuk Role
                    return '
                        <a href="' . route('roles.show', $role->id) . '" class="btn btn-info btn-sm me-1">Detail</a>
                        <a href="' . route('roles.edit', $role->id) . '" class="btn btn-primary btn-sm me-1">Edit</a>
                        <form action="' . route('roles.destroy', $role->id) . '" method="POST" class="d-inline" id="deleteRoleForm' . $role->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" 
                                    data-id="' . $role->id . '"
                                    data-name="' . $role->name . '"
                                    data-type="role" // Tambahkan identifier
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.role.index', [
            'title' => 'Manajemen Hak Akses',
            'subtitle' => 'Data Role',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil semua permission yang tersedia
        $permissions = Permission::all();

        // return $permissions;

        return view('backend.role.create', [
            'permissions' => $permissions,
            'title' => 'Manajemen Hak Akses',
            'subtitle' => 'Tambah Role Baru',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // 1. Validasi Input
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            // 'permissions' harus didefinisikan sebagai array
            'permissions' => 'nullable|array', 
            // Memastikan setiap item dalam array permissions adalah string yang ada di tabel 'permissions'
            'permissions.*' => 'string|exists:permissions,name', 
        ]);

        // 2. Buat Role
        $role = Role::create([
            'name' => $validatedData['name'],
            'guard_name' => 'web', // Pastikan guard_name benar
        ]);

        // 3. Kaitkan Permissions (Bagian KRITIS)
        // Cek apakah ada data permissions yang dikirim dari form
        if (isset($validatedData['permissions'])) {
            // Gunakan syncPermissions() dari paket Spatie
            // Data yang diterima harus berupa array of permission names atau IDs
            $role->syncPermissions($validatedData['permissions']);
        }

        // 4. Redirect
        return redirect()->route('roles.index')
                        ->with('success', 'Role **' . $role->name . '** berhasil dibuat dan izin telah dikaitkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        // Mengambil Role dan memastikan relasi 'permissions' dimuat
        $role->load('permissions');

        return view('backend.role.show', [
            'role' => $role,
            'title' => 'Manajemen Hak Akses',
            'subtitle' => 'Detail Role',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // 1. Ambil semua permissions yang tersedia
        $permissions = Permission::all();
        
        // 2. Ambil permission yang sudah dimiliki oleh role ini
        // pluck() mengambil kolom 'name', dan toArray() mengubahnya menjadi array sederhana
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('backend.role.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions, // Kirim daftar permission yang dimiliki
            'title' => 'Manajemen Hak Akses',
            'subtitle' => 'Edit Role',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            // Rule 'unique' dengan pengecualian ID Role yang sedang diedit
            'name' => 'required|string|unique:roles,name,' . $role->id, 
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // 2. Update Nama Role
        $role->update([
            'name' => $validatedData['name'],
        ]);

        // 3. Sinkronisasi Permissions (Bagian KRITIS)
        // syncPermissions() akan MENGHAPUS permissions lama dan MENYIMPAN permissions baru
        $permissionsToSync = $validatedData['permissions'] ?? [];
        $role->syncPermissions($permissionsToSync);

        // 4. Redirect
        return redirect()->route('roles.index')
                        ->with('success', 'Role **' . $role->name . '** berhasil diperbarui dan izin disinkronkan!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Hapus data Role
        // PENTING: Jika Role ini masih dikaitkan dengan User, hapus User tersebut
        // atau pisahkan Role dari User sebelum menghapus Role.
        // Jika tidak, Anda mungkin mendapatkan error foreign key constraint.
        try {
            $role->delete();

            // Redirect kembali ke halaman index dengan pesan sukses
            return redirect()->route('roles.index')
                            ->with('success', 'Role **' . $role->name . '** berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani jika ada foreign key constraint (Role masih dipakai User)
            return redirect()->route('roles.index')
                            ->with('error', 'Role **' . $role->name . '** tidak dapat dihapus karena masih digunakan oleh pengguna lain.');
        }
    }
}
