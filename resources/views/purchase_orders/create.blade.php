{{-- resources/views/purchase_orders/create.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2>{{ $title }}</h2>
    
    {{-- Notifikasi PR Sumber --}}
    @if (isset($prToConvert))
    <div class="alert alert-info border-info mb-4">
        <p class="mb-0">
            Anda sedang membuat Purchase Order dari Purchase Request: 
            <strong>{{ $prToConvert->pr_number }}</strong>. 
            Diminta oleh: 
            <strong>{{ $prToConvert->requester?->name ?? 'N/A' }}</strong> 
            (Dept: {{ $prToConvert->requester?->department?->name ?? 'N/A' }}).
        </p>
        {{-- Hidden field untuk ID PR sumber --}}
        <input type="hidden" name="source_pr_id" value="{{ $prToConvert->id }}">
    </div>
    @endif

    <form method="POST" action="{{ route('purchase_orders.store') }}">
        @csrf
        
        {{-- ================================================= --}}
        {{--          HEADER INFORMASI PURCHASE ORDER            --}}
        {{-- ================================================= --}}
        
        {{-- 1. BLOK INFORMASI DASAR PO & VENDOR --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📄 Informasi Dasar PO & Vendor</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="po_number" class="form-label">Nomor PO <span class="text-danger">*</span></label>
                            <input type="text" name="po_number" id="po_number" class="form-control" 
                                   value="{{ old('po_number', 'DRAFT-' . time()) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="po_date" class="form-label">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" name="po_date" id="po_date" class="form-control" 
                                   value="{{ old('po_date', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="po_date" class="form-label">Peminta - Departemen <span class="text-danger">*</span></label>
                            <input type="text" name="po_date" id="po_date" class="form-control" 
                                   value="{{ $prToConvert->requester->name ?? 'N/A' }} - {{  $prToConvert->requester->department->name ?? 'N/A' }}" readonly>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Pilih Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-control" required>
                                <option value="">--- Pilih Vendor ---</option>
                                @foreach ($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }} ({{ $vendor->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pr_number_source" class="form-label">Nomor PR Sumber</label>
                            <input type="text" id="pr_number_source" class="form-control" value="{{ $prToConvert->pr_number ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- 2. BLOK DETAIL PEMBAYARAN & PAJAK --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">💰 Detail Keuangan & Termin</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Termin Pembayaran --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_term" class="form-label">Metode Pembayaran (Termin) <span class="text-danger">*</span></label>
                            <select name="payment_term" id="payment_term" class="form-control" required>
                                @php $terms = ['COD', 'NET 30', 'NET 45', 'NET 60', 'Lainnya']; @endphp
                                <option value="">--- Pilih Termin ---</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term }}" {{ old('payment_term') == $term ? 'selected' : '' }}>{{ $term }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Diskon --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="discount_amount" class="form-label">Diskon (Rp)</label>
                            <input type="number" name="discount_amount" id="discount_amount" class="form-control text-end header-calc" 
                                   value="{{ old('discount_amount', 0) }}" min="0" step="0.01">
                            <small class="text-muted">Masukkan dalam Rupiah (Rp)</small>
                        </div>
                    </div>
                    
                    {{-- Pajak PPN (%) --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tax_percentage" class="form-label">Pajak (PPN %)</label>
                            <input type="number" name="tax_percentage" id="tax_percentage" class="form-control text-end header-calc" 
                                   value="{{ old('tax_percentage', 11) }}" min="0" max="100" step="0.01">
                            <small class="text-muted">Misal: 11 untuk 11% PPN</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. BLOK INFORMASI PENGIRIMAN & CATATAN --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">🚚 Pengiriman & Catatan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Alamat Pengiriman</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3">{{ old('shipping_address', $prToConvert->delivery_location ?? 'Alamat default gudang...') }}</textarea>
                            <small class="text-muted">Diambil dari lokasi pengiriman PR atau alamat default</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan PO / Keterangan Tambahan (Opsional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="shipping_cost" class="form-label">Biaya Pengiriman (Ongkir) (Rp)</label>
                            <input type="number" name="shipping_cost" id="shipping_cost" class="form-control text-end header-calc" 
                                   value="{{ old('shipping_cost', 0) }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================= --}}
        {{--           DETAIL ITEM DAN TOTAL AKHIR               --}}
        {{-- ================================================= --}}
        
        <h4 class="mt-5 mb-3">📦 Detail Item PO</h4>
        <div class="mb-4">
            <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">Tambah Item Manual</button>
        </div>

        {{-- TABEL DETAIL ITEM PO --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="itemTable">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Nama Barang/Jasa</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 15%;">Harga Satuan</th>
                        <th style="width: 15%;">Subtotal</th>
                        <th style="width: 5%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- LOOPING ITEM PR YANG DIIMPOR --}}
                    @if (isset($prToConvert) && $prToConvert->details->isNotEmpty())
                        @foreach ($prToConvert->details as $index => $item)
                        <tr class="item-row">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{-- Nama Item (editable) --}}
                                <input type="text" name="items[{{ $index }}][item_name]" class="form-control" 
                                       value="{{ old('items.' . $index . '.item_name', $item->item_name) }}" required>
                                
                                {{-- Hidden field untuk ID Detail PR sumber --}}
                                <input type="hidden" name="items[{{ $index }}][pr_detail_id]" value="{{ $item->id }}">
                            </td>
                            <td>
                                {{-- Satuan (editable) --}}
                                <input type="text" name="items[{{ $index }}][unit]" class="form-control text-center" 
                                       value="{{ old('items.' . $index . '.unit', $item->unit ?? '-') }}">
                            </td>
                            <td>
                                {{-- Qty (editable) --}}
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control text-center item-quantity" 
                                       value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" required min="1" step="any">
                            </td>
                            <td>
                                {{-- Harga Satuan (editable, diisi harga aktual vendor. Default 0) --}}
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control text-end item-price" 
                                       value="{{ old('items.' . $index . '.unit_price', $item->unit_price ?? 0) }}" required min="0" step="0.01">
                            </td>
                            <td class="text-end item-subtotal">
                                Rp 0,00 {{-- Akan diisi oleh JavaScript --}}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Hapus item ini dari PO">X</button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- Row placeholder jika tidak ada PR --}}
                        <tr class="item-row">
                             <td colspan="7" class="text-center">Silakan tambahkan item pertama Anda.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        {{-- TOTAL AKHIR (DIPISAHKAN DI KOLOM KANAN) --}}
        <div class="row mt-3 mb-5">
            <div class="col-md-7">
                {{-- Area ini bisa digunakan untuk Approval Workflow atau dokumen pendukung --}}
            </div>
            <div class="col-md-5">
                <table class="table table-sm table-borderless float-end" style="width: 100%;">
                    <tbody>
                        <tr>
                            <th class="text-end">Sub Total Barang:</th>
                            <td class="text-end" id="subTotalItemDisplay">Rp 0,00</td>
                        </tr>
                        <tr>
                            <th class="text-end">Diskon:</th>
                            <td class="text-end text-danger" id="discountDisplay">Rp 0,00</td>
                        </tr>
                        <tr>
                            <th class="text-end">Biaya Pengiriman (Ongkir):</th>
                            <td class="text-end" id="shippingDisplay">Rp 0,00</td>
                        </tr>
                        <tr>
                            <th class="text-end">DPP (Dasar Pengenaan Pajak):</th>
                            <td class="text-end text-primary" id="dppDisplay">Rp 0,00</td>
                        </tr>
                        <tr>
                            <th class="text-end">PPN (<span id="taxPercentDisplay">0</span>%):</th>
                            <td class="text-end text-success" id="taxAmountDisplay">Rp 0,00</td>
                        </tr>
                        <tr class="table-dark">
                            <th class="text-end">TOTAL KESELURUHAN (Grand Total):</th>
                            <th class="text-end" id="grandTotalDisplay">Rp 0,00</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg mt-3 mb-5">✅ Simpan Purchase Order</button>
    </form>
</div>
@endsection

@push('script-bawah') 
{{-- Gunakan @push('scripts') atau @push('js') sesuai konfigurasi layout Anda --}}
<script>
$(document).ready(function() {
    // Tentukan itemIndex. Jika sudah ada baris, mulai dari jumlah baris yang ada.
    let itemIndex = $('#itemTable tbody .item-row').length; 

    // --- FUNGSI FORMATTING DAN PERHITUNGAN ---

    function formatRupiah(number) {
        // Menggunakan Intl.NumberFormat untuk format Rupiah standar
        return 'Rp ' + (new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(number));
    }

    function calculateRowTotal($row) {
        let qty = parseFloat($row.find('.item-quantity').val()) || 0;
        let price = parseFloat($row.find('.item-price').val()) || 0;
        let subtotal = qty * price;
        $row.find('.item-subtotal').text(formatRupiah(subtotal));
        return subtotal;
    }

    function calculateGrandTotal() {
        let itemSubtotal = 0;
        
        // 1. Hitung Sub Total dari SEMUA Item yang ada
        $('#itemTable tbody .item-row').each(function() {
            let subtotalRow = calculateRowTotal($(this));
            itemSubtotal += subtotalRow;
        });

        // 2. Ambil nilai dari Header PO
        let discount = parseFloat($('#discount_amount').val()) || 0;
        let taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        let shippingCost = parseFloat($('#shipping_cost').val()) || 0;

        // 3. Hitung DPP (Dasar Pengenaan Pajak)
        let dpp = itemSubtotal + shippingCost - discount;
        if (dpp < 0) dpp = 0; 

        // 4. Hitung Pajak (PPN)
        let taxAmount = dpp * (taxPercentage / 100);

        // 5. Hitung Grand Total
        let grandTotal = dpp + taxAmount; 

        // 6. Update tampilan di Footer Total
        $('#subTotalItemDisplay').text(formatRupiah(itemSubtotal));
        $('#discountDisplay').text(formatRupiah(discount));
        $('#shippingDisplay').text(formatRupiah(shippingCost));
        $('#dppDisplay').text(formatRupiah(dpp));
        $('#taxPercentDisplay').text(taxPercentage);
        $('#taxAmountDisplay').text(formatRupiah(taxAmount));
        $('#grandTotalDisplay').text(formatRupiah(grandTotal));
    }

    // --- LISTENER PERUBAHAN INPUT ---

    // Listener untuk Qty dan Harga Item
    $('#itemTable').on('input', '.item-quantity, .item-price', function() {
        calculateGrandTotal();
    });

    // Listener untuk Input Header (Diskon, Pajak, Ongkir)
    $('#discount_amount, #tax_percentage, #shipping_cost').on('input', function() {
        calculateGrandTotal();
    });

    // --- FUNGSI PENGHAPUSAN BARIS ---
    $('#itemTable').on('click', '.remove-item', function() {
        // Minimal harus ada satu item
        if ($('#itemTable tbody .item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
            reindexItems();
        } else {
            alert("Harus ada minimal satu item dalam Purchase Order.");
        }
    });

    // --- FUNGSI PENAMBAHAN BARIS MANUAL ---
    $('#addItemBtn').on('click', function() {
        let newRow = `
            <tr class="item-row">
                <td>${itemIndex + 1}</td>
                <td>
                    <input type="text" name="items[${itemIndex}][item_name]" class="form-control" required>
                    <input type="hidden" name="items[${itemIndex}][pr_detail_id]" value="">
                </td>
                <td>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control text-center">
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center item-quantity" required min="1" step="any" value="1">
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control text-end item-price" required min="0" step="0.01" value="0">
                </td>
                <td class="text-end item-subtotal">Rp 0,00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item">X</button>
                </td>
            </tr>
        `;
        // Hapus row placeholder jika hanya ada satu row tanpa item_name
        if ($('#itemTable tbody .item-row').length === 1 && $('#itemTable tbody .item-row input[name*="item_name"]').val() === undefined) {
             $('#itemTable tbody .item-row').remove();
        }
        
        $('#itemTable tbody').append(newRow);
        itemIndex++;
        calculateGrandTotal();
        reindexItems();
    });

    // --- FUNGSI RE-INDEXING (PENTING!) ---
    function reindexItems() {
        $('#itemTable tbody .item-row').each(function(index) {
            $(this).find('td:first').text(index + 1); // Update nomor urut
            
            // Update name attribute for all inputs in the row
            $(this).find('input').each(function() {
                let currentName = $(this).attr('name');
                if (currentName) {
                    // Mengganti angka indeks di dalam string 'items[N][field]'
                    let newName = currentName.replace(/items\[\d+\]/g, `items[${index}]`);
                    $(this).attr('name', newName);
                }
            });
            // Update itemIndex untuk penambahan selanjutnya
            if (index + 1 > itemIndex) {
                 itemIndex = index + 1;
            }
        });
    }

    // Hitung total awal saat halaman dimuat
    calculateGrandTotal();
});
</script>
@endpush