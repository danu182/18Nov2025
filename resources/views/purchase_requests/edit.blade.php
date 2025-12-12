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

            <form action="{{ route('purchase_requests.update', $purchaseRequest->id) }}" method="POST">
                @csrf
                @method('PUT') 

                ### 📋 Informasi Header
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="pr_date" class="form-label">Tanggal PR</label>
                        {{-- Mempertahankan nilai lama atau nilai database --}}
                        <input type="date" class="form-control" id="pr_date" name="pr_date" 
                               value="{{ old('pr_date', $purchaseRequest->pr_date) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="requested_by" class="form-label">Diminta Oleh</label>
                        {{-- Menampilkan nama pemohon (disabled) --}}
                        <input type="text" class="form-control" 
                               value="{{ $purchaseRequest->requester->name ?? 'N/A' }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label">Tujuan Permintaan</label>
                    {{-- Mempertahankan nilai lama atau nilai database --}}
                    <textarea class="form-control" id="purpose" name="purpose" rows="2">{{ old('purpose', $purchaseRequest->purpose) }}</textarea>
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
                            {{-- Item dimuat di sini oleh JavaScript --}}
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

                <button type="button" id="addItemBtn" class="btn btn-sm btn-success mb-3"><i class="fas fa-plus"></i> Tambah Item</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Permintaan</button>
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
        
        // 🚨 MUAT DATA LAMA JIKA VALIDASI GAGAL, jika tidak, muat data database
        const oldItems = @json(old('items')); 
        const existingItems = @json($purchaseRequest->details);
        
        // Pilih sumber data: oldItems jika ada, existingItems jika tidak
        const initialData = oldItems && oldItems.length > 0 ? oldItems : existingItems;
        let itemIndex = 0; 

        // --- Utility Functions ---
        function formatNumber(number) {
            // Menggunakan IDR format
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
            subtotalHidden.value = subtotal.toFixed(2); 

            updateGrandTotal();
        }

        // --- Link Reference Functions ---
        function addReferenceRow(itemContainer, itemIdx, defaultUrl = '', defaultDescription = '', isOldData = false) {
            const container = itemContainer.querySelector('.reference-container');
            // Jika data berasal dari 'old()', kita tidak bisa mengandalkan children.length
            // Tapi karena kita menggunakan initialIndex yang terus bertambah, ini aman.
            const currentReferenceIndex = container.children.length; 

            const linkRow = document.createElement('div');
            linkRow.classList.add('input-group', 'mb-1');
            linkRow.innerHTML = `
                <span class="input-group-text p-1" style="font-size: 0.75rem;">URL:</span>
                <input type="url" name="items[${itemIdx}][references][${currentReferenceIndex}][url]" class="form-control form-control-sm me-1" placeholder="http://..." style="font-size: 0.8rem;" value="${defaultUrl}">
                <span class="input-group-text p-1" style="font-size: 0.75rem;">Desc:</span>
                <input type="text" name="items[${itemIdx}][references][${currentReferenceIndex}][description]" class="form-control form-control-sm me-1" placeholder="Deskripsi (Opsional)" style="font-size: 0.8rem;" value="${defaultDescription}">
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
        
        function addNewItemRow(itemData = null) {
            const initialIndex = itemIndex; 
            const newRow = itemsTableBody.insertRow();
            
            // Tentukan nilai default/old/existing
            const id = itemData && itemData.id ? itemData.id : ''; // ID ini penting untuk update
            const name = itemData ? itemData.item_name : '';
            const unit = itemData ? itemData.unit : '';
            const quantity = itemData ? itemData.quantity : 1;
            const unitPrice = itemData ? parseFloat(itemData.unit_price) : 0.00; 
            const subtotal = itemData ? parseFloat(itemData.subtotal) : 0.00; 

            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="items[${initialIndex}][id]" value="${id}">
                    <input type="text" name="items[${initialIndex}][item_name]" class="form-control" value="${name}" required>
                    
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
                    <input type="number" name="items[${initialIndex}][unit_price]" class="form-control item-price" value="${unitPrice.toFixed(2)}" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="text" class="form-control item-subtotal-display" value="${formatNumber(subtotal)}" disabled>
                    <input type="hidden" name="items[${initialIndex}][subtotal]" class="subtotal-hidden" value="${subtotal.toFixed(2)}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                </td>
            `;

            attachRowListeners(newRow, initialIndex);
            
            // Muat Link Referensi Lama/Existing
            const references = itemData && itemData.references ? itemData.references : [];

            if (references.length > 0) {
                // Loop melalui setiap link referensi
                references.forEach(ref => {
                    // Cek jika url tidak kosong
                    if (ref.url) { 
                        addReferenceRow(newRow, initialIndex, ref.url, ref.description);
                    }
                });
            } 
            
            // Tambahkan minimal satu baris referensi jika belum ada
            if (newRow.querySelector('.reference-container').children.length === 0) {
                addReferenceRow(newRow, initialIndex);
            }

            recalculateRowAndTotal(newRow);

            itemIndex++;
        }
        
        // --- Initialization ---
        addItemBtn.addEventListener('click', () => addNewItemRow(null));

        // Muat data item dari initialData (Old Data atau Existing Data)
        if (initialData && initialData.length > 0) {
            initialData.forEach(item => {
                addNewItemRow(item);
            });
        } else {
            // Jika tidak ada data, tambahkan satu baris baru
            addNewItemRow();
        }
        
        updateGrandTotal();
    });
</script>
@endpush