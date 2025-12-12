<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;


class PermissionCOntroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // 1. Check if the request is an AJAX request from the DataTable
        if ($request->ajax()) {
            
            // 2. Query your data (e.g., all permissions)
            $query = Permission::query(); 

            // 3. Process the query using Yajra DataTables
            return DataTables::of($query)
                // 4. Add the 'Aksi' (Action) column
                ->addColumn('action', function ($permission) {
                    $editUrl = route('permissions.edit', $permission->id);
                    $deleteUrl = route('permissions.destroy', $permission->id);
                    
                    // Create the HTML for the action buttons
                    return '
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm">Edit</a>
                        <form action="' . route('permissions.destroy', $permission->id) . '" method="POST" class="d-inline" id="deleteForm' . $permission->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" // Class baru untuk listener JS
                                    data-id="' . $permission->id . '"
                                    data-name="' . $permission->name . '"
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action']) // This tells DataTables to render the HTML, not escape it
                ->make(true);
        }

        // 5. If it's a regular GET request (not AJAX), return the view
        return view('backend.permission.index', [
            'title' => 'Permissions',
            'subtitle' => 'List Permissions',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.permission.create', [
            'title' => 'Permissions',
            'subtitle' => 'Create New Permission',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // A. Validasi Data
        $validatedData = $request->validate([
            'name' => 'required|string|unique:permissions,name', // Wajib, string, dan harus unik
            'guard_name' => 'nullable|string',
        ]);

        // B. Atur Nilai Default (Jika Perlu)
        // Jika kolom 'guard_name' tidak diisi, gunakan 'web' sebagai default
        if (empty($validatedData['guard_name'])) {
            $validatedData['guard_name'] = 'web';
        }

        // C. Penyimpanan ke Database
        // Membuat record baru di tabel 'permissions'
        Permission::create($validatedData);

        // D. Redirect dan Pemberitahuan
        // Mengarahkan pengguna kembali ke halaman daftar
        // Redirect dengan pesan sukses
        return redirect()->route('permissions.index')
                        // PESAN INI AKAN DIPERIKSA DI HALAMAN VIEW
                        ->with('success', 'Izin **' . $validatedData['name'] . '** berhasil ditambahkan!');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        // Mengembalikan view 'edit' dan mengirimkan objek permission
        return view('backend.permission.edit', [
            'permission' => $permission,
            'title' => 'Permissions',
            'subtitle' => 'Edit Izin',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        // Validasi data. Tambahkan rule 'ignore' agar nama saat ini (permission->name)
        // diizinkan, tapi nama baru harus unik terhadap semua nama permission lain.
        $validatedData = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'guard_name' => 'nullable|string',
        ]);

        // Update data
        $permission->update($validatedData);

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('permissions.index')
                         ->with('success', 'Izin **' . $permission->name . '** berhasil diperbarui!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Hapus data Izin
        $permission->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('permissions.index')
                        ->with('success', 'Izin **' . $permission->name . '** berhasil dihapus!');
    }
}
