{{-- resources/views/purchase_orders/create.blade.php (Revisi Estetika) --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <header class="mb-4 pb-3 border-bottom d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-bold text-dark">{{ $title }}</h2>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </header>

    <form method="POST" action="{{ route('purchase_orders.store') }}">
        @csrf
        
        {{-- ================================================= --}}
        {{--          BLOK SUMBER DARI PR (Alert)              --}}
        {{-- ================================================= --}}
        @if (isset($prToConvert))
        <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
            <i class="fas fa-link me-3 fa-lg"></i>
            <div>
                Anda sedang membuat Purchase Order dari **Purchase Request: {{ $prToConvert->pr_number }}**. 
                Diminta oleh: **{{ $prToConvert->requester?->name ?? 'N/A' }}** (Dept: {{ $prToConvert->requester?->department?->name ?? 'N/A' }}).
            </div>
            <input type="hidden" name="source_pr_id" value="{{ $prToConvert->id }}">
        </div>
        @endif

        {{-- ================================================= --}}
        {{--          HEADER INFORMASI PURCHASE ORDER            --}}
        {{-- ================================================= --}}
        
        <div class="row">
            {{-- KOLOM KIRI: PO DETAILS & VENDOR --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4 border-start border-primary border-5">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-file-invoice me-2"></i> Informasi Dasar PO</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="po_number" class="form-label">Nomor PO <span class="text-danger">*</span></label>
                                <input type="text" name="po_number" id="po_number" class="form-control" 
                                        value="{{ old('po_number', 'DRAFT-' . time()) }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="po_date" class="form-label">Tanggal PO <span class="text-danger">*</span></label>
                                <input type="date" name="po_date" id="po_date" class="form-control" 
                                        value="{{ old('po_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="vendor_id" class="form-label">Pilih Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" id="vendor_id" class="form-select" required>
                                    <option value="">--- Pilih Vendor ---</option>
                                    @foreach ($vendors ?? [] as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }} ({{ $vendor->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="requester_info" class="form-label">Peminta / Departemen</label>
                                <input type="text" id="requester_info" class="form-control bg-light" 
                                        value="{{ $prToConvert->requester->name ?? 'N/A' }} - {{  $prToConvert->requester->department->name ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FINANCIAL & DELIVERY --}}
            <div class="col-md-6">
                <div class="card shadow-sm mb-4 border-start border-success border-5">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-success fw-bold"><i class="fas fa-truck-loading me-2"></i> Pengiriman & Termin</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="required_delivery_date" class="form-label">Target Tanggal Kirim <span class="text-danger">*</span></label>
                                <input type="date" name="required_delivery_date" id="required_delivery_date" class="form-control" 
                                        value="{{ old('required_delivery_date', $prToConvert->required_date ?? '') }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="terms_of_payment" class="form-label">Metode Pembayaran (Termin) <span class="text-danger">*</span></label>
                                <select name="terms_of_payment" id="terms_of_payment" class="form-select" required>
                                    @php $terms = ['COD', 'NET 30', 'NET 45', 'NET 60', 'Lainnya']; @endphp
                                    <option value="">--- Pilih Termin ---</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term }}" {{ old('terms_of_payment') == $term ? 'selected' : '' }}>{{ $term }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address" class="form-label">Alamat Pengiriman</label>
                                <textarea name="shipping_address" id="shipping_address" class="form-control" rows="2">{{ old('shipping_address', $prToConvert->delivery_location ?? 'Alamat default gudang...') }}</textarea>
                                <small class="text-muted">Diambil dari lokasi PR atau alamat default.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BLOK DETAIL KEUANGAN TAMBAHAN --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i> Kalkulasi Keuangan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Mata Uang --}}
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="currency" class="form-label">Mata Uang <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-select" required>
                                @php $currencies = ['IDR', 'SGD', 'USD', 'Lainnya']; @endphp
                                <option value="">--- Pilih Mata Uang ---</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency }}" {{ old('currency', 'IDR') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Biaya Pengiriman (Ongkir) --}}
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="shipping_cost" class="form-label">Biaya Pengiriman (Rp)</label>
                            <input type="number" name="shipping_cost" id="shipping_cost" class="form-control text-end header-calc" 
                                    value="{{ old('shipping_cost', 0) }}" min="0" step="0.01">
                        </div>
                    </div>
                    
                    {{-- Diskon --}}
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="discount_amount" class="form-label">Diskon (Rp)</label>
                            <input type="number" name="discount_amount" id="discount_amount" class="form-control text-end header-calc" 
                                    value="{{ old('discount_amount', 0) }}" min="0" step="0.01">
                        </div>
                    </div>
                    
                    {{-- Pajak PPN (%) --}}
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tax_percentage" class="form-label">Pajak (PPN %)</label>
                            <input type="number" name="tax_percentage" id="tax_percentage" class="form-control text-end header-calc" 
                                    value="{{ old('tax_percentage', 11) }}" min="0" max="100" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. BLOK CATATAN --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Catatan (Opsional)</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Masukkan catatan tambahan atau persyaratan khusus...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>


        {{-- ================================================= --}}
        {{--           DETAIL ITEM DAN TOTAL AKHIR               --}}
        {{-- ================================================= --}}
        
        <h4 class="mt-5 mb-3 fw-bold"><i class="fas fa-cubes me-2"></i> Detail Item PO</h4>
        <div class="mb-4 d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" id="addItemBtn"><i class="fas fa-plus me-1"></i> Tambah Item Manual</button>
        </div>

        {{-- TABEL DETAIL ITEM PO --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle" id="itemTable">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th>Nama Barang/Jasa</th>
                        <th class="text-center" style="width: 10%;">Satuan</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-center" style="width: 15%;">Harga Satuan</th>
                        <th class="text-center" style="width: 15%;">Subtotal</th>
                        <th class="text-center" style="width: 5%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- LOOPING ITEM PR YANG DIIMPOR --}}
                    @if (isset($prToConvert) && $prToConvert->details->isNotEmpty())
                        @foreach ($prToConvert->details as $index => $item)
                        <tr class="item-row">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <input type="text" name="items[{{ $index }}][item_name]" class="form-control form-control-sm" 
                                        value="{{ old('items.' . $index . '.item_name', $item->item_name) }}" required>
                                <input type="hidden" name="items[{{ $index }}][pr_detail_id]" value="{{ $item->id }}">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][unit]" class="form-control form-control-sm text-center" 
                                        value="{{ old('items.' . $index . '.unit', $item->unit ?? '-') }}">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm text-center item-quantity" 
                                        value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" required min="1" step="any">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm text-end item-price" 
                                        value="{{ old('items.' . $index . '.unit_price', $item->unit_price ?? 0) }}" required min="0" step="0.01">
                            </td>
                            <td class="text-end fw-bold item-subtotal">
                                Rp 0,00 {{-- Akan diisi oleh JavaScript --}}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-item" title="Hapus item ini dari PO"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- Row placeholder jika tidak ada PR --}}
                        <tr class="item-row">
                             <td colspan="7" class="text-center text-muted py-3">Silakan tambahkan item pertama Anda.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        {{-- TOTAL AKHIR (DIPISAHKAN DI KOLOM KANAN) --}}
        <div class="row mt-3 mb-5">
            <div class="col-md-7">
                {{-- Placeholder untuk dokumen pendukung --}}
            </div>
            <div class="col-md-5">
                <div class="p-3 border rounded shadow-sm">
                    <h5 class="fw-bold mb-3 text-secondary">Ringkasan Total</h5>
                    <table class="table table-sm table-borderless" style="width: 100%;">
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
                                <th class="text-end">Biaya Pengiriman:</th>
                                <td class="text-end" id="shippingDisplay">Rp 0,00</td>
                            </tr>
                            <tr>
                                <th class="text-end text-primary">DPP:</th>
                                <td class="text-end text-primary fw-bold" id="dppDisplay">Rp 0,00</td>
                            </tr>
                            <tr>
                                <th class="text-end">PPN (<span id="taxPercentDisplay">0</span>%):</th>
                                <td class="text-end text-success" id="taxAmountDisplay">Rp 0,00</td>
                            </tr>
                            <tr class="table-primary border-top border-2 border-dark">
                                <th class="text-end h5 mb-0 pt-2">GRAND TOTAL:</th>
                                <th class="text-end h5 mb-0 pt-2" id="grandTotalDisplay">Rp 0,00</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-grid mb-5">
            <button type="submit" class="btn btn-primary btn-lg mt-3 shadow">
                <i class="fas fa-save me-2"></i> Simpan Purchase Order
            </button>
        </div>
    </form>
</div>
@endsection

@push('script-bawah') 
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

        // 3. Hitung DPP (Dasar Pengenaan Pajak): Subtotal Barang + Ongkir - Diskon
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
            // Jika item yang dihapus adalah item terakhir, tampilkan placeholder
            if ($('#itemTable tbody .item-row').length === 1 && $(this).closest('tr').hasClass('item-row')) {
                $(this).closest('tr').remove();
                $('#itemTable tbody').append('<tr class="item-row"><td colspan="7" class="text-center text-muted py-3">Silakan tambahkan item pertama Anda.</td></tr>');
                calculateGrandTotal();
            } else {
                alert("Harus ada minimal satu item dalam Purchase Order.");
            }
        }
    });

    // --- FUNGSI PENAMBAHAN BARIS MANUAL ---
    $('#addItemBtn').on('click', function() {
        // Cek dan hapus placeholder jika ada
        if ($('#itemTable tbody .item-row').length === 1 && $('#itemTable tbody .item-row td').attr('colspan') === '7') {
            $('#itemTable tbody .item-row').remove();
        }
        
        let currentIndex = itemIndex; // Gunakan itemIndex saat ini

        let newRow = `
            <tr class="item-row">
                <td class="text-center">${currentIndex + 1}</td>
                <td>
                    <input type="text" name="items[${currentIndex}][item_name]" class="form-control form-control-sm" required>
                    <input type="hidden" name="items[${currentIndex}][pr_detail_id]" value="">
                </td>
                <td>
                    <input type="text" name="items[${currentIndex}][unit]" class="form-control form-control-sm text-center">
                </td>
                <td>
                    <input type="number" name="items[${currentIndex}][quantity]" class="form-control form-control-sm text-center item-quantity" required min="1" step="any" value="1">
                </td>
                <td>
                    <input type="number" name="items[${currentIndex}][unit_price]" class="form-control form-control-sm text-end item-price" required min="0" step="0.01" value="0">
                </td>
                <td class="text-end fw-bold item-subtotal">Rp 0,00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
        
        $('#itemTable tbody').append(newRow);
        itemIndex++; // Naikkan counter global
        calculateGrandTotal();
        reindexItems();
    });

    // --- FUNGSI RE-INDEXING (PENTING!) ---
    function reindexItems() {
        let maxIndex = 0;
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
            maxIndex = index;
        });
        // Update itemIndex untuk penambahan selanjutnya
        itemIndex = maxIndex + 1;
    }

    // Hitung total awal saat halaman dimuat
    calculateGrandTotal();
});
</script>
@endpush