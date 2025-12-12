<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Pilih semua kolom yang akan ditampilkan
            $query = Company::select(['id', 'code', 'name', 'email', 'phone', 'pic']); 

            return DataTables::of($query)
                ->addColumn('action', function ($company) {
                    // Tombol Aksi (Edit dan Delete)
                    $editUrl = route('companies.edit', $company->id);
                    $showUrl = route('companies.show', $company->id);
                    $deleteUrl = route('companies.destroy', $company->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a>
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm me-1">Edit</a>
                        
                        <form action="' . $deleteUrl . '" method="POST" class="d-inline" id="deleteCompanyForm' . $company->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" 
                                    data-id="' . $company->id . '"
                                    data-name="' . $company->name . '"
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.company.index', [
            'title' => 'Master Data',
            'subtitle' => 'Data Perusahaan',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.company.create', [
            'title' => 'Master Data',
            'subtitle' => 'Tambah Perusahaan Baru',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            // Validasi 'code' dan 'name' harus unik (unique)
            'code' => 'required|string|max:20|unique:companies,code',
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'pic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        // 2. Simpan ke Database
        Company::create($validated);

        // 3. Redirect dengan Pesan Sukses
        return redirect()->route('companies.index')->with('success', 'Perusahaan **' . $validated['name'] . '** berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('backend.company.show', [
            'company' => $company,
            'title' => 'Master Data',
            'subtitle' => 'Detail Perusahaan',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('backend.company.edit', [
            'company' => $company, // Kirim objek company ke view
            'title' => 'Master Data',
            'subtitle' => 'Edit Perusahaan: ' . $company->name,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            // Pengecualian: Abaikan ID perusahaan yang sedang diedit
            'code' => 'required|string|max:20|unique:companies,code,' . $company->id, 
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'pic' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        // 2. Update Data
        $company->update($validated);

        // 3. Redirect dengan Pesan Sukses
        return redirect()->route('companies.index')
                        ->with('success', 'Perusahaan **' . $company->name . '** berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // Cek apakah perusahaan ini masih dikaitkan dengan User manapun
        if ($company->users()->exists()) {
            return redirect()->route('companies.index')
                            ->with('error', 'Perusahaan **' . $company->name . '** tidak dapat dihapus karena masih digunakan oleh minimal satu pengguna. Hapus atau pindahkan pengguna tersebut terlebih dahulu.');
        }
        
        // Jika aman, hapus data Company
        try {
            $company_name = $company->name;
            $company->delete();

            // Redirect kembali ke halaman index dengan pesan sukses
            return redirect()->route('companies.index')
                            ->with('success', 'Perusahaan **' . $company_name . '** berhasil dihapus!');
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('companies.index')
                            ->with('error', 'Terjadi kesalahan saat menghapus perusahaan.');
        }
    }
}
