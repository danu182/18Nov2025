@extends('layouts.app') 

{{-- Asumsi Anda memiliki layout utama yang mendefinisikan sections('styles', 'content', 'scripts') --}}

@section('styles')
    {{-- Wajib: CSS DataTables Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    {{-- Custom Style untuk Badge dan List Items (Sama seperti yang Anda berikan) --}}
    <style>
        .badge.bg-success { background-color: #198754 !important; color: white; }
        .badge.bg-danger { background-color: #dc3545 !important; color: white; }
        .badge.bg-warning { background-color: #ffc107 !important; color: #212529; }
        .badge.bg-secondary { background-color: #6c757d !important; color: white; }
        .badge.bg-info { background-color: #0dcaf0 !important; color: #212529; }
        /* Style untuk membuat item list terlihat rapi di dalam sel tabel */
        .list-unstyled { padding-left: 0; list-style: none; margin-bottom: 0; }
        .list-unstyled-item { border-bottom: 1px solid #eee; padding: 3px 0; font-size: 0.85rem; }
        .list-unstyled-item:last-child { border-bottom: none; }
    </style>
@endsection

@section('content')
<div class="container-fluid pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- Menggunakan data dari array yang diasumsikan dikirim oleh index() jika ada --}}
        <h2>💰 {{ $title ?? 'Daftar Purchase Orders (PO)' }}</h2>
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Buat PO Baru
        </a>
    </div>

    <hr>

    {{-- Filter Section --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">🔍 Opsi Pencarian dan Filter</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search_input" class="form-label">Cari PO</label>
                    <input type="text" name="search" id="search_input" class="form-control" placeholder="Nomor PO atau Nama Vendor...">
                </div>
                <div class="col-md-3">
                    <label for="status_select" class="form-label">Status Persetujuan</label>
                    <select name="status" id="status_select" class="form-select">
                        <option value="">-- Semua Status --</option>
                        @foreach(['Draft', 'Submitted', 'Approved', 'Rejected'] as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="button" id="applyFilter" class="btn btn-secondary flex-fill">Terapkan Filter</button>
                    <button type="button" id="clearFilter" class="btn btn-light border flex-fill">Reset</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchase Order Table --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="po-table" style="width:100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th> 
                            <th style="width: 15%">PO Number</th>
                            <th style="width: 15%">Vendor</th>
                            <th style="width: 10%">PR Number</th>
                            <th style="width: 15%">Items (Detail)</th>
                            <th style="width: 10%">Total Amount</th>
                            <th style="width: 10%">Tgl. Delivery</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data dimuat oleh DataTables AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {{-- Wajib: jQuery, Skrip DataTables --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script> --}}

    @push('script-bawah')

        <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>


        <script>
            $(document).ready(function() {
                // Asumsi: Route DataTables JSON endpoint adalah 'purchase_orders.data'
                var dataUrl = '{{ route('purchase_orders.data') }}'; 
                
                var table = $('#po-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: dataUrl,
                        data: function (d) {
                            // Mengirimkan nilai filter kustom ke Controller@data
                            d.status = $('#status_select').val();
                            d.search = $('#search_input').val(); // Menggunakan input kustom untuk search global
                        }
                    },
                    columns: [
                        // Kolom # (ID PO, hanya untuk ordering)
                        { data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false },
                        
                        // Kolom PO Number (HTML Link)
                        { data: 'po_number_link', name: 'po_number' },
                        
                        // Kolom Vendor (Data relasi)
                        { data: 'vendor_name', name: 'vendor.name' },
                        
                        // Kolom PR Number (Data relasi)
                        { data: 'pr_number', name: 'purchaseRequest.pr_number' },
                        
                        // Kolom Items List (HTML/Unordered List)
                        { data: 'items_list', name: 'items_list', orderable: false, searchable: false },

                        // Kolom Total Amount (Formatted)
                        { data: 'total_amount_formatted', name: 'total_amount' },
                        
                        // Kolom Delivery Date (Formatted)
                        { data: 'delivery_date_formatted', name: 'required_delivery_date' }, 
                        
                        // Kolom Status (Badge HTML)
                        { data: 'status_badge', name: 'status' },
                        
                        // Kolom Aksi (Tombol HTML)
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    order: [[0, 'desc']], // Urutkan berdasarkan ID PO terbaru
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Opsional: Bahasa Indonesia
                    }
                });

                // 4. Menerapkan Filter Kustom
                $('#applyFilter').on('click', function() {
                    table.draw();
                });

                // 5. Membersihkan Filter Kustom
                $('#clearFilter').on('click', function() {
                    $('#status_select').val('');
                    $('#search_input').val('');
                    // DataTables.draw() akan mengambil nilai filter yang sudah kosong dan refresh
                    table.draw(); 
                });
                
                // Menggunakan Enter key pada search input untuk filter
                $('#search_input').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        table.draw();
                    }
                });
            });
        </script>
    @endpush
        
@endsection