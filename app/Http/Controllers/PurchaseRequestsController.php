<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequests;
use App\Http\Controllers\Controller;
use App\Models\PrAapproval;
use App\Models\PrItemReference;
use App\Models\PurchaseRequestDetails;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log; // Untuk debugging
use Illuminate\Support\Facades\Redirect;

class PurchaseRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Cek apakah ini permintaan AJAX dari DataTables
        if ($request->ajax()) {
            
            // 2. Query Data dengan Eager Loading
            $query = PurchaseRequests::with([
                'requester', 
                'requester.company',
                'requester.department',
                'details', // Eager load relasi item/detail PR
                'currentApprover'
            ])->select('purchase_requests.*'); // Select semua kolom PR

            // 3. Proses Data Melalui DataTables
            return DataTables::of($query)
                
                // --- Menambahkan Kolom Kustom (dengan Null Checks) ---
                
                // 3.0. Kolom ID (#)
                ->addColumn('DT_RowIndex', function ($pr) {
                    return $pr->id; // Menggunakan ID PR sebagai index (bisa diganti dengan $pr->id untuk penomoran)
                })

                // 3a. Kolom Tanggal (Formatted)
                ->addColumn('pr_date_formatted', function ($pr) {
                    // Pastikan pr_date adalah instance Carbon atau gunakan \Carbon\Carbon::parse() jika masih string
                    return $pr->pr_date ? $pr->pr_date->format('d M Y') : '-'; 
                })

                // 3b. Kolom Diminta Oleh (Akses Relasi Requester)
                ->addColumn('requester_name', function ($pr) {
                    // Menggunakan optional chaining (?) untuk null check
                    return $pr->requester?->name ?? '-';
                })

                // 3c. Kolom Perusahaan (Relasi Requester -> Company)
                ->addColumn('requester_perusahaan', function ($pr) {
                    // Menggunakan optional chaining (?) untuk mengakses relasi multi-level dengan aman (PHP 8+)
                    return $pr->requester?->company?->name ?? '-';
                })

                // 3d. Kolom Departemen (Relasi Requester -> Department)
                ->addColumn('requester_departement', function ($pr) {
                    // Menggunakan optional chaining (?) untuk mengakses relasi multi-level dengan aman (PHP 8+)
                    return $pr->requester?->department?->name ?? '-';
                })

                // 3e. KOLOM ITEM YANG DIPERBAIKI (Menggunakan $pr->details)
                ->addColumn('items_list', function ($pr) {
                    $itemsHtml = '';
                    $limit = 2;

                    // Menggunakan relasi 'details' yang sudah di eager load
                    $details = $pr->details; 
                    
                    if ($details->isEmpty()) {
                        return '-';
                    }
                    // Tampilkan detail dalam format JSON untuk debugging
                    // return $details->toJson();
                    
                    // Pastikan properti 'name' dan 'qty' sudah benar di model Detail Item
                    foreach ($details->take($limit) as $item) {
                        // Jika name atau qty null/kosong, kode ini yang akan menghasilkan output di atas
                        $itemName = $item->item_name. ' - ' ?? 'Nama Item Hilang'; // <-- Nama Item Hilang
                        $itemQty = $item->quantity.' '.$item->unit  ?? 0;                  // <-- (0)
                        $itemsHtml .= '<li class=."list-group-item".>' . e($itemName) . ' (' . e($itemQty) . ')</li>';
                    }

                    if ($details->count() > $limit) {
                        $remaining = $details->count() - $limit;
                        $itemsHtml .= '<li><small>+ ' . $remaining . ' item lainnya...</small></li>';
                    }

                    return '<ul class="list-unstyled">' . $itemsHtml . '</ul>';
                })
                
                // 3f. Kolom Total Jumlah (Formatted Rupiah)
                ->addColumn('total_amount_formatted', function ($pr) {
                    $amount = $pr->total_amount ?? 0;
                    // Anda mungkin perlu fungsi helper Rupiah yang lebih spesifik, tapi number_format sudah cukup
                    return 'Rp ' . number_format($amount, 2, ',', '.'); 
                })

                // 3g. Kolom Status (Badge HTML)
                ->addColumn('status_badge', function ($pr) {
                    $class = match ($pr->status) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Pending' => 'warning',
                        'Draft' => 'secondary',
                        default => 'secondary',
                    };
                    return '<span class="badge bg-' . $class . '">' . ($pr->status ?? 'N/A') . '</span>';
                })

                // 3h. Kolom Approver Saat Ini (Akses Relasi Approver)
                ->addColumn('current_approver_name', function ($pr) {
                    return $pr->currentApprover?->name ?? '-';  
                })

                // 3i. Kolom Aksi (Tombol HTML)
                ->addColumn('action', function ($pr) { 
                    $showUrl = route('purchase_requests.show', $pr->id);
                    $editUrl = route('purchase_requests.edit', $pr->id);
                    $deleteUrl = route('purchase_requests.destroy', $pr->id);

                    $buttons = '<a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a>';
                    
                    
                    if ($pr->status == 'Draft') {
                        $buttons .= '<a href="' . $editUrl . '" class="btn btn-primary btn-sm me-1">Edit</a>';
                        $buttons .= '
                            <form action="' . $deleteUrl . '" method="POST" class="d-inline">
                                ' . method_field('delete') . csrf_field() . '
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin ingin menghapus Purchase Request ini?\')">Hapus</button>
                            </form>';
                    }
                    return $buttons;
                })
                
                // 4. Definisikan Kolom yang Berisi HTML Murni
                ->rawColumns(['status_badge', 'action', 'items_list'])
                
                // 5. Tambahkan kolom relasi untuk filtering/ordering
                // Perlu diperhatikan: DataTables tidak dapat mengurutkan atau mencari kolom kustom yang dibuat dengan addColumn.
                // Untuk kolom relasi, gunakan . (dot notation) pada 'name' jika Anda ingin mengaktifkan sorting/searching,
                // tetapi ini memerlukan penyesuaian di query Eloquent, tidak hanya di sini.
                ->with(['requested_by_id' => 'requested_by', 'current_approver_id' => 'current_approver_id'])

                // 6. Akhiri pemrosesan DataTables
                ->make(true);
        }

        // 7. Tampilkan View (Jika bukan permintaan AJAX)
        return view('purchase_requests.index', [
            'title' => 'Purchase Request',
            'subtitle' => 'Daftar Permintaan Pembelian',
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user)
    {

        // return $user;


        // Anda bisa meneruskan data user yang login jika diperlukan
        $user = auth()->user(); 
        
        return view('purchase_requests.create', [
            'user' => $user,
            'title' => 'Create Purchase Request',
            'subtitle' => 'Buat Permintaan Pembelian Baru'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'pr_date' => 'required|date',
            'purpose' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            // Validasi Link Referensi
            'items.*.references' => 'nullable|array',
            'items.*.references.*.url' => 'nullable|url|max:500',
            'items.*.references.*.description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Generate Nomor PR (Logika sederhana)
            // Gunakan PurchaseRequest (singular)
            $lastPr = PurchaseRequests::orderBy('id', 'desc')->first();
            $prNumber = 'PR/' . str_pad(($lastPr ? $lastPr->id : 0) + 1, 3, '0', STR_PAD_LEFT) . '/' . date('Y');
            
            // Hitung Total Jumlah Item
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;
            }

            // Cari pengguna pertama dengan role 'Approver Level 1'
            // $firstApprover = User::role('approve-pr-level-1')->first();
            // if (!$firstApprover) {
            //     throw new \Exception("Tidak ditemukan pengguna dengan peran 'Approver Level 1' untuk persetujuan awal.");
            // }

            $firstApprover = User::whereHas('roles', function ($query) {
                $query->where('name', 'approve-pr-level-1');
            })->first();

            if (!$firstApprover) {
                throw new \Exception("Tidak ditemukan pengguna dengan peran 'Approver Level 1' untuk persetujuan awal.");
            }

            // 3. Simpan Data Header
            $pr = PurchaseRequests::create([
                'pr_number' => $prNumber,
                'pr_date' => $request->pr_date,
                'requested_by' => auth()->id(),
                'purpose' => $request->purpose,
                'total_amount' => $totalAmount, // Pastikan tidak ada duplikasi
                'current_approver_id' => $firstApprover->id,
            ]);

            // 4. Simpan Data Detail & Link Referensi (Looping)
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                
                // 4a. Buat Item Detail PR (menggunakan relasi create() untuk mendapatkan objek detail)
                $prDetail = $pr->details()->create([ 
                    'item_name' => $item['item_name'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                // 4b. Simpan Link Referensi (jika ada)
                if (!empty($item['references'])) {
                    $referencesToSave = [];
                    foreach ($item['references'] as $ref) {
                        if (!empty($ref['url'])) { 
                            $referencesToSave[] = new PrItemReference([
                                'url' => $ref['url'],
                                'description' => $ref['description'] ?? null,
                            ]);
                        }
                    }
                    
                    // Simpan semua referensi terkait ke prDetail
                    if (!empty($referencesToSave)) {
                        $prDetail->references()->saveMany($referencesToSave);
                    }
                }
            }
            
            // 5. Simpan Riwayat Approval (Level 0: Submitter)
            PrAapproval::create([
                'purchase_request_id' => $pr->id,
                'level' => 0,
                'approver_id' => auth()->id(), 
                'action' => 'Submitted',
                'action_at' => now(),
                'notes' => 'Permintaan diajukan oleh pemohon.',
            ]);

            DB::commit();

            return redirect()->route('purchase_requests.index')->with('success', 'Permintaan Pembelian **' . $prNumber . '** berhasil dibuat dan menunggu persetujuan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PR Creation Failed: " . $e->getMessage()); // Log error untuk debugging
            return redirect()->back()->withInput()->with('error', 'Gagal membuat Permintaan Pembelian. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $purchaseRequest= PurchaseRequests::with(
                [
                    'details.references', // Item detail dan referensinya
                    'requester',
                    'requester.company',
                    'requester.department',
                    'approvals', // Untuk menampilkan riwayat persetujuan
                ])
                ->find($id);
            // return $purchaseRequest;
            
            // Format total amount untuk tampilan
            $totalAmountFormatted = 'Rp ' . number_format($purchaseRequest->total_amount ?? 0, 2, ',', '.');
            
            // Format tanggal
            $prDateFormatted = $purchaseRequest->pr_date ? $purchaseRequest->pr_date->format('d F Y') : '-';

            return view('purchase_requests.show', [
                'title' => 'Detail Purchase Request',
                'subtitle' => $purchaseRequest->pr_number,
                'purchaseRequest' => $purchaseRequest,
                'totalAmountFormatted' => $totalAmountFormatted,
                'prDateFormatted' => $prDateFormatted,
                // 'pr_id' => $purchaseRequest->id
            ]);
        }
        catch (\Exception $e) {
                        return redirect()->route('purchase_requests.index')->with('success', 'Permintaan Pembelian **' . $id . '** Tidak ditemukan!');

        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ststus='Draft';
        $purchaseRequest= PurchaseRequests::with(
            [
                'details',
                'details.references',
                'requester',
                'requester.company',
                'requester.department',
                'approvals',
                
            ])
            ->where('id',$id)
            ->where('status',$ststus)
            ->first();

            // return $purchaseRequest; 

            // 1. Cek jika PR tidak ditemukan
            if (!$purchaseRequest) {
                // Misalnya, redirect kembali atau tampilkan 404
                abort(404, 'Purchase Request tidak ditemukan.');
            }
            
            // 2. Cek status PR
            if ($purchaseRequest->status !== $ststus) {
                return redirect()->route('purchase_requests.index')->with('error', 
                    'Purchase Request No. ' . $purchaseRequest->pr_number . ' tidak dapat diedit karena statusnya sudah ' . $purchaseRequest->status . '.'
                );
            }

            // Pastikan nama relasi item dan referensi sesuai dengan skema DB Anda
            // Jika di DB: PurchaseRequest -> items (relasi one-to-many) -> references (relasi one-to-many pada item)
            // Maka harusnya: 'items.references'
            
            return view('purchase_requests.edit', [
                'title' => 'Edit Purchase Request',
                'subtitle' => 'Ubah Data PR No. ' . $purchaseRequest->pr_number,
                'purchaseRequest' => $purchaseRequest,
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) 
    {
        $purchaseRequest = PurchaseRequests::find($id);

        if (!$purchaseRequest) {
            return redirect()->route('purchase_requests.index')->with('error', 'Purchase Request tidak ditemukan.');
        }

        // 1. Validasi Data
        $request->validate([
            // 'pr_date' => 'required|date',
            'purpose' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            // Validasi Link Referensi
            'items.*.references' => 'nullable|array',
            'items.*.references.*.url' => 'nullable|url|max:500',
            'items.*.references.*.description' => 'nullable|string|max:255',
        ]);

        // 2. Cek Status Ulang (Guardrail)
        if ($purchaseRequest->status !== 'Draft') {
            return redirect()->route('purchase_requests.index')->with('error', 'Update gagal: PR hanya dapat diubah jika statusnya Draft.');
        }

        try {
            // Mulai Transaksi Database
            DB::beginTransaction();

            // 3. Proses Item Detail dan Hitung Total
            $itemsToKeepIds = [];
            $totalAmount = 0;

            foreach ($request->items as $itemData) {
                // Hitung Subtotal di sisi backend untuk keamanan
                $subtotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemData['subtotal'] = $subtotal; // Tambahkan subtotal ke array itemData

                $referencesData = $itemData['references'] ?? []; // Ambil data referensi sebelum update/create
                unset($itemData['references']); // Hapus 'references' agar tidak ikut di-update ke tabel detail

                if (isset($itemData['id']) && $itemData['id']) {
                    // a. UPDATE item yang sudah ada
                    $detail = PurchaseRequestDetails::findOrFail($itemData['id']);
                    $detail->update($itemData);
                } else {
                    // b. CREATE item baru
                    // Membuat item baru dan secara otomatis mengaitkannya dengan PR
                    $detail = $purchaseRequest->details()->create($itemData);
                }
                
                $itemsToKeepIds[] = $detail->id; // Tambahkan ID ke daftar item yang dipertahankan
                $totalAmount += $subtotal;


                // 4. Proses Link Referensi untuk Item Detail saat ini
                $refsToKeepIds = [];
                foreach ($referencesData as $refData) {
                    // Abaikan referensi yang URL-nya kosong
                    if (empty(trim($refData['url']))) {
                        continue;
                    }
                    
                    // Gunakan 'id' jika ada, menandakan referensi lama
                    if (isset($refData['id']) && $refData['id']) {
                        // UPDATE referensi yang sudah ada
                        $reference = PurchaseRequestReference::findOrFail($refData['id']);
                        $reference->update([
                            'url' => $refData['url'],
                            'description' => $refData['description'],
                        ]);
                        $refsToKeepIds[] = $reference->id;
                    } else {
                        // CREATE referensi baru (tanpa 'id')
                        $reference = $detail->references()->create([
                            'url' => $refData['url'],
                            'description' => $refData['description'],
                        ]);
                        $refsToKeepIds[] = $reference->id;
                    }
                }
                
                // Hapus referensi lama yang tidak ada di data baru (dihapus user)
                $detail->references()
                    ->whereNotIn('id', $refsToKeepIds)
                    ->delete();
            }

            // 5. DELETE Item Detail yang dihapus (dan secara otomatis menghapus referensinya)
            // Asumsi: Model PurchaseRequestDetails menggunakan `hasMany` untuk references
            // dan memiliki setting `onDelete('cascade')` pada migration, atau Anda
            // menangani penghapusan referensi secara manual di sini jika diperlukan.
            $deletedItems = $purchaseRequest->details()
                ->whereNotIn('id', $itemsToKeepIds)
                ->get();
            
            // Hapus referensi terkait sebelum menghapus detail (jika cascade tidak diset di DB)
            foreach ($deletedItems as $deletedItem) {
                $deletedItem->references()->delete();
            }

            // Hapus item detail yang lama
            $purchaseRequest->details()
                ->whereNotIn('id', $itemsToKeepIds)
                ->delete();

            // 6. Update Header PR dengan Total Baru
            $purchaseRequest->update([
                // 'pr_date' => $request->pr_date,
                'purpose' => $request->purpose,
                'total_amount' => $totalAmount, 
                'requested_by' => auth()->id(), // Pastikan kolom ini diizinkan di fillable
            ]);

            // Commit Transaksi
            DB::commit();

            return redirect()->route('purchase_requests.index')->with('success', 'Purchase Request ' . $purchaseRequest->pr_number . ' berhasil diperbarui!');

        } catch (\Exception $e) {
            // Rollback Transaksi jika ada error
            DB::rollBack();
            
            // Log Error dan kembalikan pesan ke user
            Log::error("Gagal update PR " . $purchaseRequest->id . ": " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui PR. Silakan coba lagi atau hubungi administrator.')->withErrors(['system_error' => $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $purchaseRequest = PurchaseRequests::find($id);

        if (!$purchaseRequest) {
            return redirect()->route('purchase_requests.index')->with('error', 'Purchase Request tidak ditemukan.');
        }

        // 1. Cek Status (Guardrail)
        if ($purchaseRequest->status !== 'Draft') {
            return redirect()->route('purchase_requests.index')->with('error', 'Penghapusan gagal: PR hanya dapat dihapus jika statusnya Draft.');
        }

        try {
            DB::beginTransaction();

            // 2. Hapus Item Detail (dan secara otomatis menghapus referensi)
            // Pastikan relasi 'details' ada di model PurchaseRequests
            $details = $purchaseRequest->details;
            
            foreach ($details as $detail) {
                // Hapus semua referensi yang terkait dengan item detail ini
                $detail->references()->delete();
            }

            // Hapus semua item detail PR
            $purchaseRequest->details()->delete();

            // 3. Hapus Header PR
            $prNumber = $purchaseRequest->pr_number;
            $purchaseRequest->delete();

            DB::commit();

            return redirect()->route('purchase_requests.index')->with('success', "Purchase Request $prNumber berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus PR " . $purchaseRequest->id . ": " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus PR. Silakan coba lagi atau hubungi administrator.');
        }
    }


    public function processApproval(Request $request, PurchaseRequests $pr)
    {

        // return $pr;
        // 1. Validasi Input Aksi
        $request->validate([
            'action' => 'required|in:Approved,Rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        // PENTING: Pastikan hanya pengguna yang berhak yang bisa melakukan aksi
        if ($pr->status != 'Draft') {
            return redirect()->back()->with('error', 'Anda tidak berhak atau PR ini tidak dalam status Pending untuk disetujui.');
        }

        // Cek apakah pengguna yang login memiliki peran yang sesuai dengan level approval saat ini
        $currentLevel = $pr->approvals()->where('action', '!=', 'Submitted')->max('level') ?? 0;

        // return $currentLevel;
        // $requiredRole = 'Approver Level ' . ($currentLevel + 1);
        $requiredRole = 'approve-pr-level-' . ($currentLevel + 1);

        if (!auth()->user()->hasRole($requiredRole)) {
            return redirect()->back()->with('error', "Aksi gagal. Anda tidak memiliki peran '{$requiredRole}' yang dibutuhkan.");
        }

        
        // 2. Tentukan Level dan Approver Berikutnya (Logika Sederhana)
        $currentLevel = $pr->approvals()->where('action', '!=', 'Submitted')->max('level') ?? 0;
        $nextLevel = $currentLevel + 1;
        $action = $request->action;

        DB::beginTransaction();
        try {
            // 3. Simpan Riwayat Aksi Saat Ini
            PrAapproval::create([
                'purchase_request_id' => $pr->id,
                'level' => $nextLevel,
                'approver_id' => auth()->id(),
                'action' => $action,
                'notes' => $request->notes,
                'action_at' => now(),
            ]);

            if ($action == 'Approved') {
                
                // ASUMSI: Approval hanya 2 Level (misal Level 1 = Supervisor, Level 2 = Manager)
                $nextApproverId = null; // ID user untuk level berikutnya

                if ($nextLevel == 1) {
                    // Jika Level 1 baru saja Approve, cari approver Level 2
                   $nextApprover = User::whereHas('roles', function ($query) {
                        $query->where('name', 'approve-pr-level-2');
                    })->first();
                    
                    if (!$nextApprover) {
                        throw new \Exception("Tidak ditemukan pengguna dengan peran 'Approver Level 2'.");
                    }
                    $nextApproverId = $nextApprover->id; 
                    $newStatus = 'Approved';
                } elseif ($nextLevel >= 2) { // Anggap level 2 adalah final
                    // Jika Level 2 (Final) sudah Approve
                    $newStatus = 'Approved';
                    $pr->approval_date = now();
                }

                $pr->status = $newStatus ?? 'Pending';
                $pr->current_approver_id = $nextApproverId;
                
            } else { // Jika Rejected
                $pr->status = 'Rejected';
                $pr->current_approver_id = null;
            }

            $pr->save();
            DB::commit();

            return redirect()->route('purchase_requests.indexApproval')->with('success', "PR {$pr->pr_number} berhasil di-{$action}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approval process failed for PR #{$pr->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }



    public function indexApproval(Request $request)
    {
        if ($request->ajax()) {
            $user_id = auth()->id();
            
            $query = PurchaseRequests::with(['requester', 'currentApprover'])
                                    ->where('current_approver_id', $user_id) // Hanya PR yang harus disetujui oleh user ini
                                    ->where('status', 'Pending') // Hanya PR yang statusnya masih Pending
                                    ->select('purchase_requests.*'); 

            return DataTables::of($query)
                // ... (Tambahkan addColumn yang sama seperti index() untuk status, requester, dll.) ...
                ->addColumn('action', function ($pr) {
                    // Tombol aksi di sini diarahkan ke halaman SHOW, yang akan berisi form Approve/Reject
                    $showUrl = route('purchase_requests.show', $pr->id);
                    return '<a href="' . $showUrl . '" class="btn btn-warning btn-sm">Review & Proses</a>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('purchase_requests.approval_list', [
            'title' => 'Persetujuan PR',
            'subtitle' => 'Daftar Menunggu Persetujuan',
        ]);
    }



    public function printPr($id)
    {
        // Ambil data PR dengan relasi yang dibutuhkan
        $purchaseRequest = PurchaseRequests::with([
            'details.references', 
            'requester',
            'requester.company',
            'requester.department',
            'approvals.approver',
        ])->find($id);

        if (!$purchaseRequest) {
            abort(404, 'Purchase Request tidak ditemukan.');
        }

        // Hitung level approval saat ini (opsional, untuk tampilan tanda tangan)
        $lastApproval = $purchaseRequest->approvals()->where('action', 'Approved')->orderBy('level', 'desc')->first();
        $currentLevel = $lastApproval ? $lastApproval->level : 0;
        $nextLevel = $currentLevel + 1;
        
        // Format total amount
        $totalAmountFormatted = 'Rp ' . number_format($purchaseRequest->total_amount ?? 0, 2, ',', '.');


        // Memuat view khusus untuk cetak
        return view('purchase_requests.print', [
            'purchaseRequest' => $purchaseRequest,
            'totalAmountFormatted' => $totalAmountFormatted,
            'nextLevel' => $nextLevel,
            'title' => 'Cetak PR ' . $purchaseRequest->pr_number,
        ]);
    }


}
