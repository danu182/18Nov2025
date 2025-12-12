<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Pilih semua kolom yang akan ditampilkan
            $query = Department::select(['id','code', 'name']); 

            return DataTables::of($query)
                ->addColumn('action', function ($department) {
                    // Tombol Aksi (Edit dan Delete)
                    $editUrl = route('departments.edit', $department->id);
                    $showUrl = route('departments.show', $department->id);
                    $deleteUrl = route('departments.destroy', $department->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a>
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm me-1">Edit</a>
                        
                        <form action="' . $deleteUrl . '" method="POST" class="d-inline" id="deleteDepartmentForm' . $department->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" 
                                    data-id="' . $department->id . '"
                                    data-name="' . $department->name . '"
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('backend.departments.index', [
            'title' => 'Master Data',
            'subtitle' => 'Data Departemen',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.departments.create', [
            'title' => 'Master Data',
            'subtitle' => 'Tambah Department Baru',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:10|unique:departments,code', // Kode boleh kosong, tapi jika diisi harus unik
        ]);

        // Simpan ke Database
        Department::create($validated);

        // Redirect dengan Pesan Sukses
        return redirect()->route('departments.index')->with('success', 'Departemen **' . $validated['name'] . '** berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        // Jika Anda ingin menampilkan juga daftar User di departemen ini:
        // $department->load('users'); 

        return view('backend.departments.show', [
            'department' => $department,
            'title' => 'Master Data',
            'subtitle' => 'Detail Departemen',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        return view('backend.departments.edit', [
            'department' => $department, // Kirim objek department ke view
            'title' => 'Master Data',
            'subtitle' => 'Edit Departemen: ' . $department->name,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            // Pengecualian: Abaikan ID departemen yang sedang diedit
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id, 
            'code' => 'nullable|string|max:10|unique:departments,code,' . $department->id,
        ]);

        // 2. Update Data
        $department->update($validated);

        // 3. Redirect dengan Pesan Sukses
        return redirect()->route('departments.index')
                        ->with('success', 'Departemen **' . $department->name . '** berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Cek apakah departemen ini masih dikaitkan dengan User
        if ($department->users()->exists()) {
            // Jika ada User, redirect ke index Department dengan pesan ERROR
            return redirect()->route('departments.index') 
                            ->with('error', 'Departemen **' . $department->name . '** tidak dapat dihapus karena masih digunakan oleh minimal satu pengguna. Hapus atau pindahkan pengguna tersebut terlebih dahulu.');
        }
        
        // Jika aman, coba hapus
        try {
            $department_name = $department->name;
            $department->delete();

            // Redirect ke index Department dengan pesan SUKSES
            return redirect()->route('departments.index')
                            ->with('success', 'Departemen **' . $department_name . '** berhasil dihapus!');
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            // Redirect ke index Department dengan pesan ERROR
            return redirect()->route('departments.index')
                         ->with('error', 'Gagal Hapus. Database Error: ' . $e->getMessage()); // Tampilkan pesan error
        }
    }
}
