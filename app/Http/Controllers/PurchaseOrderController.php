<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\PurchaseApprovalLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequests;
use App\Models\Vendor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log Facade
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{

    private function generatePoNumber(): string
    {
        // 1. Tentukan Prefix dan Periode
        $prefix = 'PO/PT'; // Ganti 'PT' dengan kode entitas Anda jika perlu
        $yearMonthDay = now()->format('Y/m/d'); // Format tahun/bulan (contoh: 2025/12)
        $searchPattern = $prefix . '/' . $yearMonthDay . '/%';

        // 2. Cari Nomor PO Terakhir untuk bulan ini
        // Ambil PO dengan pola yang cocok dan urutkan secara descending untuk mendapatkan yang terbesar
        $lastPo = PurchaseOrder::where('po_number', 'like', $searchPattern)
                              ->orderBy('po_number', 'desc')
                              ->first();

        $newSequence = 1;

        if ($lastPo) {
            // Jika ada PO terakhir:
            // a. Ambil nomor urut (bagian terakhir setelah slash)
            $parts = explode('/', $lastPo->po_number);
            $lastSequence = (int)end($parts); // Konversi ke integer

            // b. Naikkan nomor urut
            $newSequence = $lastSequence + 1;
        }

        // 3. Format dan Gabungkan
        // Pad nomor urut dengan nol di depan (misalnya: 0001, 0010, 0100)
        $formattedSequence = str_pad($newSequence, 4, '0', STR_PAD_LEFT);

        // Gabungkan menjadi nomor PO lengkap
        return $prefix . '/' . $yearMonthDay . '/' . $formattedSequence;
    }


    public function data(Request $request)
    {
        // 1. Inisialisasi Query dengan Eager Loading
        $query = PurchaseOrder::with([
            'vendor',
            'purchaseRequest',
            'details',
            'creator',
            'purchaseApprovalLogs',
        ])->select('purchase_orders.*');

        // 2. Implementasi Filter Dasar
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'like', '%' . $search . '%')
                ->orWhereHas('vendor', function($q_vendor) use ($search) {
                    $q_vendor->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // 3. Proses Data Melalui DataTables
        return DataTables::of($query)
            
            // Kolom 1: ID PO
            ->addColumn('DT_RowIndex', function ($po) {
                return $po->id; 
            })
            
            // Kolom 2: Nomor PO (Link ke Detail)
            ->addColumn('po_number_link', function ($po) {
                // KOREKSI UTAMA: Menggunakan 'purchase_orders.show'
                $showUrl = route('purchase_orders.show', $po->id); 
                return '<a href="' . $showUrl . '"><strong>' . e($po->po_number) . '</strong></a>';
            })

            // Kolom 3: Vendor
            ->addColumn('vendor_name', function ($po) {
                return $po->vendor?->name ?? '-';
            })
            
            // Kolom 4: PR Number
            ->addColumn('pr_number', function ($po) {
                return $po->purchaseRequest?->pr_number ?? '-';
            })

            // Kolom 5: Items List (Maks 2 item)
            ->addColumn('items_list', function ($po) {
                $itemsHtml = '';
                $limit = 2;
                $details = $po->details;
                
                if ($details->isEmpty()) {
                    return '-';
                }
                
                foreach ($details->take($limit) as $item) {
                    $itemName = e($item->item_name); 
                    $itemQty = e($item->quantity) . ' ' . e($item->unit) ?? 0;
                    $itemsHtml .= '<li class="list-unstyled-item">' . $itemName . ' (' . $itemQty . ')</li>';
                }

                if ($details->count() > $limit) {
                    $remaining = $details->count() - $limit;
                    $itemsHtml .= '<li><small>+ ' . $remaining . ' item lainnya...</small></li>';
                }

                return '<ul class="list-unstyled">' . $itemsHtml . '</ul>';
            })
            
            // Kolom 6: Total Amount (Formatted)
            ->addColumn('total_amount_formatted', function ($po) {
                $amount = $po->total_amount ?? 0;
                return ($po->currency ?? 'IDR') . ' ' . number_format($amount, 2, ',', '.'); 
            })

            // Kolom 7: Delivery Date (Formatted)
            ->addColumn('delivery_date_formatted', function ($po) {
                return $po->required_delivery_date ? 
                            \Carbon\Carbon::parse($po->required_delivery_date)->format('d M Y') : 
                            '-';
            })

            // Kolom 8: Status (Badge HTML)
            ->addColumn('status_badge', function ($po) {
                $class = match ($po->status) {
                    'Approved' => 'success',
                    'Submitted' => 'info',
                    'Rejected' => 'danger',
                    'Draft' => 'secondary',
                    default => 'secondary',
                };
                return '<span class="badge bg-' . $class . '">' . ($po->status ?? 'N/A') . '</span>';
            })

            // Kolom 9: Aksi (Tombol HTML)
            ->addColumn('action', function ($po) { 
                $showUrl = route('purchase_orders.show', $po->id);
                $editUrl = route('purchase_orders.edit', $po->id);

                $buttons = '<a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a>';
                
                if ($po->status == 'Draft') {
                    $buttons .= '<a href="' . $editUrl . '" class="btn btn-warning btn-sm me-1">Edit</a>';
                }

                return $buttons;
            })
            
            // 4. Definisikan Kolom yang Berisi HTML Murni
            ->rawColumns(['po_number_link', 'status_badge', 'action', 'items_list'])
            
            // 5. Akhiri pemrosesan DataTables
            ->make(true);
    }



    public function index()
    {
        // Mengembalikan view dengan data title/subtitle
        // return view('purchase_orders.index', [
        //     'title' => 'Purchase Order',
        //     'subtitle' => 'Daftar Permintaan Pembelian',
        // ]);

        return view('purchase_orders.index');
    }

        
    public function createFromPR($pr_id)
    {
        // 1. Ambil Purchase Request beserta detail yang diperlukan
        $purchaseRequest = PurchaseRequests::with([
            'details.references', 
            'requester.company', 
            'requester.department'
            // Tambahkan relasi lain yang relevan untuk PO
        ])->find($pr_id);


        $purchaseRequest->load(['details.references', 'requester.company', 'requester.department']);


        $vendor= Vendor::all();

        return view('purchase_orders.create', [
                'title' => 'Buat Purchase Order Baru',
                'subtitle'=>'buat PO baru', 
                'prToConvert' => $purchaseRequest, // Data PR tetap dikirim
                // Anda mungkin perlu mengirim daftar Vendor juga di sini
                'vendors'=> $vendor,
            ]);



        // Cek jika PR tidak ditemukan
        if (!$purchaseRequest) {
            return redirect()->route('purchase_requests.index')->with('error', 'Purchase Request tidak ditemukan.');
        }

        // Cek kondisi lain (misalnya, apakah PR sudah disetujui, belum di-PO-kan, dll.)
        if ($purchaseRequest->status !== 'Approved') {
            return redirect()->back()->with('error', 'Hanya PR yang sudah disetujui yang dapat dibuat PO.');
        }

        // 2. Siapkan data awal untuk form PO
        // Anda bisa membuat array atau objek khusus untuk passing data
        $poData = [
            'pr_id' => $purchaseRequest->id,
            'pr_number' => $purchaseRequest->pr_number,
            'items' => $purchaseRequest->details, // Detail PR akan menjadi item PO
            'requester_name' => $purchaseRequest->requester->name ?? 'N/A',
            'company_name' => $purchaseRequest->requester->company->name ?? 'N/A',
            'department_name' => $purchaseRequest->requester->department->name ?? 'N/A',
            'total_amount' => $purchaseRequest->total_amount,
            // ... data lain yang perlu diisi otomatis ke form PO
        ];

        // 3. Tampilkan view pembuatan PO dengan data PR
        return view('purchase_orders.create', [
            'title' => 'Buat Purchase Order Baru',
            'subtitle' => 'Berdasarkan PR No. ' . $purchaseRequest->pr_number,
            'poData' => $poData,
            'purchaseRequest' => $purchaseRequest, // Opsi: kirim seluruh objek PR
        ]);
    }



    public function store(Request $request)
    {
        // --- 1. & 2. VALIDASI DATA (Header & Items) ---
        // Gabungkan validasi agar pesan error muncul sekaligus untuk Header dan Item
        
        // Aturan Validasi
        $rules = [
            // Header
            'vendor_id'             => 'required|exists:vendors,id',
            'pr_id'                 => 'nullable|exists:purchase_requests,id',
            'currency'              => 'required|string|in:IDR,USD,EUR',
            'po_date'               => 'required|date',
            'required_delivery_date'=> 'nullable|date|after_or_equal:po_date',
            'terms_of_payment'      => 'nullable|string|max:50',
            'shipping_address'      => 'nullable|string',
            'tax_amount'            => 'required|numeric|min:0',
            'subtotal'              => 'required|numeric|min:0',
            'total_amount'          => 'required|numeric|min:0', // Hapus gte:subtotal sementara jika tax bisa negatif/diskon, atau biarkan jika standar
            'items'                 => 'required|array|min:1',
            
            // Detail Items (Array)
            'items.*.description'   => 'required|string|max:255',
            'items.*.unit'          => 'nullable|strin|mx:50',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.price'         => 'required|numeric|min:0.01',
        ];

        // Custom Messages (Opsional, agar pesan lebih user friendly)
        $messages = [
            'vendor_id.required' => 'Vendor harus dipilih.',
            'items.required'     => 'Minimal harus ada satu item barang.',
            'items.*.description.required' => 'Deskripsi item baris ke-:position wajib diisi.',
            'items.*.qty.min'    => 'Qty baris ke-:position minimal 1.',
        ];

        // Jalankan Validasi (Otomatis redirect back jika gagal)
        $validated = $request->validate($rules, $messages);

        // --- 3. PROSES PENYIMPANAN DALAM TRANSAKSI ---
        try {
            DB::beginTransaction();

            // 3.1 Buat Nomor PO
            $poNumber = 'PO-' . now()->format('Ymd') . '-' . (PurchaseOrder::count() + 1);

            // 3.2 Simpan Header
            $purchaseOrder = PurchaseOrder::create([
                'pr_id'                 => $validated['pr_id'],
                'po_number'             => $poNumber,
                'vendor_id'             => $validated['vendor_id'],
                'po_date'               => $validated['po_date'],
                'required_delivery_date'=> $validated['required_delivery_date'],
                'terms_of_payment'      => $validated['terms_of_payment'],
                'shipping_address'      => $validated['shipping_address'],
                'currency'              => $validated['currency'],
                'subtotal'              => $validated['subtotal'],
                'tax_amount'            => $validated['tax_amount'],
                'total_amount'          => $validated['total_amount'],
                'status'                => 'draft',
                'created_by'            => auth()->check() ? auth()->id() : null,
            ]);
            
            // 3.3 Simpan Detail Item
            foreach ($validated['items'] as $item) {
                $lineTotal = $item['qty'] * $item['price'];
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'description'       => $item['description'],
                    'qty'               => $item['qty'],
                    'unit_price'        => $item['price'],
                    'line_total'        => $lineTotal,

                    'po_id'             => $purchaseOrder->id,
                    'item_name'         => $item['description'],
                    'quantity'          => $item['qty'],
                    'unit'          => $item['unit'],
                    'unit_price'        => $item['price'],
                    'subtotal'          =>$lineTotal,



                ]);
            }



             // 5. Simpan Riwayat Approval (Level 0: Submitter) purchase_approval_logs
            PurchaseApprovalLog::create([               
                'purchase_order_id' => $purchaseOrder->id,
                'user_id' => auth()->id(), 
                'action'    => 'SUBMIT',
                'level' => 0,
                'comment'=>'Permintaan diajukan oleh pemohon.',

            ]);

            DB::commit();

            return redirect()->route('purchase_orders.show', $purchaseOrder->id)
                            ->with('success', 'Purchase Order berhasil dibuat: ' . $poNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log error asli untuk developer
            Log::error('Gagal menyimpan PO: ' . $e->getMessage()); 
            
            // Redirect kembali dengan pesan error umum untuk user & Input lama
            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan sistem saat menyimpan data. Silakan coba lagi. (' . $e->getMessage() . ')')
                            ->withInput();
        }
    }


    // app/Http/Controllers/PurchaseOrderController.php

    public function show($id)
    {
        $po = PurchaseOrder::with([
            'vendor',
            'purchaseRequest.requester', // Sertakan pembuat PR
            'details',                 // Detail item PO
            'creator',     
            'purchaseApprovalLogs.user', // Log persetujuan dan user yang menyetujui
        ])->findOrFail($id);

        // return $po;
        // Anda bisa menambahkan logic untuk otorisasi di sini (e.g., apakah user boleh melihat PO ini)

        return view('purchase_orders.show', [
            'title' => 'Detail Purchase Order',
            'po' => $po,
            // Contoh: Data status untuk tampilan badge
            'statusMap' => [
                'Approved' => 'success',
                'Submitted' => 'info',
                'Rejected' => 'danger',
                'Draft' => 'secondary',
            ]
        ]);
    }

    public function edit($id)
    {

    }



}
