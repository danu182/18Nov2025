@extends('layouts.app')
@section('content')
<div class="container">
    <h2>{{ $title }} - {{ $subtitle }}</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            
            {{-- Bagian Pemberitahuan Error Validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5>Kesalahan Validasi:</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('purchase_requests.store') }}" method="POST">
                @csrf

                ### 📋 Informasi Header
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="pr_date" class="form-label">Tanggal PR</label>
                        {{-- Mempertahankan nilai lama atau default tanggal hari ini --}}
                        <input type="date" class="form-control" id="pr_date" name="pr_date" 
                               value="{{ old('pr_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="requested_by" class="form-label">Diminta Oleh</label>
                        {{-- Asumsi $user tersedia dari controller --}}
                        <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="requested_by" class="form-label">company</label>
                        {{-- Asumsi $user tersedia dari controller --}}
                        <input type="text" class="form-control" value="{{ $user->company->name ?? 'N/A'}} - {{  $user->department->name ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label">Tujuan Permintaan</label>
                    {{-- Mempertahankan nilai lama --}}
                    <textarea class="form-control" id="purpose" name="purpose" rows="2">{{ old('purpose') }}</textarea>
                </div>
                
                <hr>

                ### 📝 Detail Item Permintaan & Referensi
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Nama Barang/Jasa & Link Referensi</th>
                                <th style="width: 120px;">Satuan</th>
                                <th style="width: 120px;">Qty</th>
                                <th style="width: 180px;">Harga Satuan (Est.)</th>
                                <th style="width: 200px;">Subtotal</th>
                                <th style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris Item akan dimuat/ditambahkan di sini oleh JavaScript --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end">**TOTAL KESELURUHAN**</td>
                                <td><input type="text" id="grandTotal" class="form-control" value="0.00" disabled></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" id="addItemBtn" class="btn btn-sm btn-success mb-3"><i class="fa fa-plus"></i> Tambah Item</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Submit Permintaan</button>
                    <a href="{{ route('purchase_requests.index') }}" class="btn btn-secondary">Batal</a>
                </div>

            </form>
            
        </div>
    </div>
</div>

@endsection

@push('script-bawah')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemsTableBody = document.querySelector('#itemsTable tbody');
        const addItemBtn = document.querySelector('#addItemBtn');
        const grandTotalInput = document.querySelector('#grandTotal');
        
        // 🚨 MUAT DATA LAMA DARI SESI JIKA VALIDASI GAGAL
        const oldItems = @json(old('items')); 
        let itemIndex = 0; 

        // --- Utility Functions ---
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        function updateGrandTotal() {
            let total = 0;
            itemsTableBody.querySelectorAll('.subtotal-hidden').forEach(input => {
                total += parseFloat(input.value) || 0; 
            });
            grandTotalInput.value = formatNumber(total);
        }

        function recalculateRowAndTotal(row) {
            const qtyInput = row.querySelector('.item-quantity');
            const priceInput = row.querySelector('.item-price');
            const subtotalDisplay = row.querySelector('.item-subtotal-display');
            const subtotalHidden = row.querySelector('.subtotal-hidden');

            if (!qtyInput || !priceInput || !subtotalDisplay || !subtotalHidden) return; 

            const qty = parseInt(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0; 
            
            const subtotal = qty * price;
            
            subtotalDisplay.value = formatNumber(subtotal);
            subtotalHidden.value = subtotal.toFixed(2); // Simpan nilai desimal yang benar untuk submission

            updateGrandTotal();
        }

        // --- Link Reference Functions ---
        // Menambahkan parameter defaultUrl dan defaultDescription
        function addReferenceRow(itemContainer, itemIdx, defaultUrl = '', defaultDescription = '') {
            const container = itemContainer.querySelector('.reference-container');
            const referenceIndex = container.children.length; // Index unik untuk array references[n]

            const linkRow = document.createElement('div');
            linkRow.classList.add('input-group', 'mb-1');
            linkRow.innerHTML = `
                <span class="input-group-text p-1" style="font-size: 0.75rem;">URL:</span>
                <input type="url" name="items[${itemIdx}][references][${referenceIndex}][url]" class="form-control form-control-sm me-1" placeholder="http://..." style="font-size: 0.8rem;" value="${defaultUrl}">
                <span class="input-group-text p-1" style="font-size: 0.75rem;">Desc:</span>
                <input type="text" name="items[${itemIdx}][references][${referenceIndex}][description]" class="form-control form-control-sm me-1" placeholder="Deskripsi (Opsional)" style="font-size: 0.8rem;" value="${defaultDescription}">
                <button type="button" class="btn btn-danger btn-sm remove-reference-btn" style="padding: 0.1rem 0.3rem;"><i class="fas fa-minus"></i></button>
            `;

            container.appendChild(linkRow);

            linkRow.querySelector('.remove-reference-btn').addEventListener('click', function() {
                linkRow.remove();
            });
        }

        // --- Core Row Creation and Listeners ---
        function attachRowListeners(newRow, initialIndex) {
            const qtyInput = newRow.querySelector('.item-quantity');
            const priceInput = newRow.querySelector('.item-price');
            const removeBtn = newRow.querySelector('.remove-item-btn');
            const addReferenceBtn = newRow.querySelector('.add-reference-btn');

            qtyInput.addEventListener('input', () => recalculateRowAndTotal(newRow));
            priceInput.addEventListener('input', () => recalculateRowAndTotal(newRow));

            removeBtn.addEventListener('click', function() {
                newRow.remove();
                updateGrandTotal(); 
            });
            
            addReferenceBtn.addEventListener('click', function() {
                addReferenceRow(newRow, initialIndex); 
            });
        }
        
        // Menambahkan parameter itemData untuk memuat data lama
        function addNewItemRow(itemData = null) {
            const initialIndex = itemIndex;
            const newRow = itemsTableBody.insertRow();
            
            // Tentukan nilai default/old untuk input item
            const itemName = itemData && itemData.item_name ? itemData.item_name : '';
            const unit = itemData && itemData.unit ? itemData.unit : '';
            const quantity = itemData && itemData.quantity ? itemData.quantity : 1;
            const unitPrice = itemData && itemData.unit_price ? itemData.unit_price : 0.00;
            // Gunakan subtotal dari data lama jika ada, jika tidak, hitung (0.00)
            const subtotal = itemData && itemData.subtotal ? itemData.subtotal : quantity * unitPrice; 

            newRow.innerHTML = `
                <td>
                    <input type="text" name="items[${initialIndex}][item_name]" class="form-control" value="${itemName}" required>
                    
                    <div class="mt-2 p-2 border rounded bg-light">
                        <small class="d-block mb-1">Link Referensi:</small>
                        <div class="reference-container">
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-info mt-1 add-reference-btn" style="font-size: 0.7rem;"><i class="fas fa-link"></i> Tambah Link</button>
                    </div>
                </td>
                <td>
                    <input type="text" name="items[${initialIndex}][unit]" class="form-control" value="${unit}">
                </td>
                <td>
                    <input type="number" name="items[${initialIndex}][quantity]" class="form-control item-quantity" value="${quantity}" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${initialIndex}][unit_price]" class="form-control item-price" value="${parseFloat(unitPrice).toFixed(2)}" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="text" class="form-control item-subtotal-display" value="${formatNumber(subtotal)}" disabled>
                    <input type="hidden" name="items[${initialIndex}][subtotal]" class="subtotal-hidden" value="${parseFloat(subtotal).toFixed(2)}">
                </td>
                <td>
                    {{-- Menggunakan fas fa-trash untuk memastikan ikon Font Awesome 5/6 muncul --}}
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                </td>
            `;

            attachRowListeners(newRow, initialIndex);
            
            // 🚀 Muat Link Referensi Lama
            if (itemData && itemData.references && itemData.references.length > 0) {
                // Hapus satu baris link default kosong yang otomatis dibuat jika ada data lama
                const references = itemData.references;
                references.forEach(ref => {
                    // Cek jika url tidak kosong
                    if (ref.url) { 
                        addReferenceRow(newRow, initialIndex, ref.url, ref.description);
                    }
                });
            } 
            
            // Tambahkan minimal satu baris referensi (kosong atau default)
            // Ini akan memastikan selalu ada satu baris input referensi yang muncul
            if (newRow.querySelector('.reference-container').children.length === 0) {
                addReferenceRow(newRow, initialIndex);
            }

            recalculateRowAndTotal(newRow); // Hitung ulang total
            itemIndex++;
        }
        
        // --- Initialization ---
        addItemBtn.addEventListener('click', () => addNewItemRow());

        // 🚀 INI ADALAH LOGIKA UTAMA UNTUK MEMPERTAHANKAN DATA LAMA
        if (oldItems && oldItems.length > 0) {
            oldItems.forEach(item => {
                addNewItemRow(item);
            });
        } else {
            // Jika tidak ada data lama, tambahkan satu baris baru
            if (itemsTableBody.rows.length === 0) {
                addNewItemRow();
            }
        }
        
        updateGrandTotal();
    });
</script>
@endpush