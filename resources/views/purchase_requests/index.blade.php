@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="page-inner">
        <header class="mb-4">
            <h2>{{ $title }}</h2>
            <p class="text-muted">{{ $subtitle }}</p>
        </header>
        
        <hr>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Daftar Purchase Request</h4>
                        <a href="{{ route('purchase_requests.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Buat PR Baru
                        </a>
                    </div>
                    <div class="card-body">
                        
                        {{-- Notifikasi Sukses/Error --}}
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover" id="prDatatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>No. PR</th>
                                        <th>Tanggal</th>
                                        <th>Diminta Oleh</th>
                                        <th>Perusahaan</th>
                                        <th>Departemen</th>
                                        <th>Item Di-request</th>
                                        <th>Total (Rp)</th>
                                        <th>Status</th>
                                        <th>Approver Saat Ini</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data akan diisi oleh DataTables --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- @push('scripts-bawah') --}}
@push('script-bawah')
{{-- Pastikan Anda sudah menyertakan library DataTables JS di layout Anda --}}
{{-- <script src="path/to/datatables.min.js"></script>  --}}

{{-- <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/js/plugin/dataTables.bootstrap4.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script> --}}
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>

{{-- <script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        $('#prDatatable').DataTable({
            processing: true, 
            serverSide: true, 
            
            ajax: {
                // Menggunakan url()->current() adalah aman jika metode index() dipanggil
                url: '{!! url()->current() !!}', 
            },
            
            // Definisikan kolom (HARUS ADA 8 KOLOM, SESUAI HEADER HTML)
            columns: [
                { data: 'id', name: 'id', width: '5%' },
                { data: 'pr_number', name: 'pr_number' },
                
                // 1. Kolom Tanggal (pr_date_formatted) - Sebelumnya Hilang
                { data: 'pr_date_formatted', name: 'pr_date_formatted', orderable: true, searchable: true },
                
                // 2. Kolom Diminta Oleh (requester_name)
                { data: 'requester_name', name: 'requester_name', orderable: false, searchable: false }, 
                
               // PASTIKAN NAMA INI SAMA PERSIS DENGAN ADDCOLUMN DI PHP
                { data: 'requester_perusahaan', name: 'requester_perusahaan'}, 
                { data: 'requester_departement', name: 'requester_departement', orderable: true, searchable: true }, 

                
                // 3. Kolom departemen
                { data: 'total_amount_formatted', name: 'total_amount_formatted', orderable: false, searchable: false }, 
                
                // 4. Kolom Status (status_badge)
                { data: 'status_badge', name: 'status_badge', orderable: true, searchable: true }, 
                
                // 5. Kolom Approver Saat Ini (current_approver_name)
                { data: 'current_approver_name', name: 'current_approver_name', orderable: false, searchable: false }, 
                
                // 6. Kolom Aksi
                { data: 'action', name: 'action', orderable: false, searchable: false }, 
            ],
            
            // Pengaturan tambahan
            order: [[0, 'desc']] // Urutkan berdasarkan ID terbaru
        });
    });
</script> --}}


<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        $('#prDatatable').DataTable({
            processing: true, 
            serverSide: true, 
            
            ajax: {
                url: '{!! url()->current() !!}', 
            },
            
            // Definisikan kolom (HARUS SESUAI DENGAN 11 HEADER HTML)
            columns: [
                // 1. # (menggunakan DT_RowIndex)
                { data: 'DT_RowIndex', name: 'id', width: '5%' },
                // 2. No. PR
                { data: 'pr_number', name: 'pr_number' },
                // 3. Tanggal
                { data: 'pr_date_formatted', name: 'pr_date_formatted', orderable: true, searchable: true },
                // 4. Diminta Oleh
                { data: 'requester_name', name: 'requester_name', orderable: false, searchable: false }, 
                // 5. Perusahaan
                { data: 'requester_perusahaan', name: 'requester_perusahaan', orderable: false, searchable: false }, 
                // 6. Departemen
                { data: 'requester_departement', name: 'requester_departement', orderable: true, searchable: true }, 

                // 7. KOLOM BARU (Item Di-request)
                { data: 'items_list', name: 'items_list', orderable: true, searchable: true }, 
                
                // 8. Total (Rp)
                { data: 'total_amount_formatted', name: 'total_amount_formatted', orderable: false, searchable: false }, 
                // 9. Status
                { data: 'status_badge', name: 'status_badge', orderable: true, searchable: true }, 
                // 10. Approver Saat Ini
                { data: 'current_approver_name', name: 'current_approver_name', orderable: false, searchable: false }, 
                // 11. Aksi
                { data: 'action', name: 'action', orderable: false, searchable: false }, 
            ],
            
            // Pengaturan tambahan
            order: [[0, 'desc']] // Urutkan berdasarkan kolom pertama (ID) terbaru
        });
    });
</script>


@endpush