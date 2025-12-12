<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Pilih semua kolom yang akan ditampilkan
            $query = Vendor::select([
                'id',
                'name',
                'email',
                'phone',
                'address',
                'contact_person', 
                'npwp', 
                'notes',
                'is_active', 
            ]); 

            return DataTables::of($query)
                ->addColumn('action', function ($vendor) {
                    // Tombol Aksi (Edit dan Delete)
                    $editUrl = route('vendor.edit', $vendor->id);
                    $showUrl = route('vendor.show', $vendor->id);
                    $deleteUrl = route('vendor.destroy', $vendor->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a>
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm me-1">Edit</a>
                        
                        <form action="' . $deleteUrl . '" method="POST" class="d-inline" id="deleteCompanyForm' . $vendor->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" 
                                    data-id="' . $vendor->id . '"
                                    data-name="' . $vendor->name . '"
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('backend.vendor.index', [
            'title' => 'Master Data',
            'subtitle' => 'Data Vendor',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // return "Asdasd";
        return view('backend.vendor.create', [
            'title' => 'Master Data',
            'subtitle' => 'Tambah Vendor Baru',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' =>'nullable|string|max:255', 
            'npwp' => 'nullable|string|max:255',  
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // 2. Simpan ke Database
        Vendor::create($validated);

        // 3. Redirect dengan Pesan Sukses
        return redirect()->route('vendor.index')->with('success', 'Vendor **' . $validated['name'] . '** berhasil ditambahkan!');

    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        return view('backend.vendor.edit', [
            'vendor' => $vendor, // Kirim objek vendor ke view
            'title' => 'Master Data',
            'subtitle' => 'Edit vendor: ' . $vendor->name,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            // Pengecualian: Abaikan id perusahaan yang sedang diedit            
            'name' => 'required|string|max:255|unique:vendors,name,' . $vendor->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' =>'nullable|string|max:255', 
            'npwp' => 'nullable|string|max:255',  
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // 2. Update Data
        $vendor->update($validated);

        // 3. Redirect dengan Pesan Sukses
        return redirect()->route('vendor.index')
                        ->with('success', 'vendor **' . $vendor->name . '** berhasil diperbarui!');
   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        // Cek apakah perusahaan ini masih dikaitkan dengan purchaseOrder yang sudah ada
        if ($vendor->purchaseOrder()->exists()) {
            return redirect()->route('vendor.index')
                            ->with('error', 'Vendor **' . $vendor->name . '** tidak dapat dihapus karena masih digunakan oleh minimal satu pengguna. Hapus atau pindahkan pengguna tersebut terlebih dahulu.');
        }
        
        // Jika aman, hapus data Company
        try {
            $vendor_name = $vendor->name;
            $vendor->delete();

            // Redirect kembali ke halaman index dengan pesan sukses
            return redirect()->route('vendor.index')
                            ->with('success', 'Perusahaan **' . $vendor_name . '** berhasil dihapus!');
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('vendor.index')
                            ->with('error', 'Terjadi kesalahan saat menghapus perusahaan.');
        }
    }
}
