{{-- resources/views/purchase_requests/approval_list.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        {{-- ... Header & Breadcrumbs ... --}}
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $subtitle }}</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Ini adalah daftar Purchase Request yang membutuhkan persetujuan Anda.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="prApprovalTable">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">No. PR</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Diminta Oleh</th>
                                        <th scope="col">Total (Rp)</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
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

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    // ... (Logika SweetAlert Success/Error sama seperti di index) ...

    // Inisialisasi DataTable untuk Daftar Approval
    $('#prApprovalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! route('purchase_requests.indexApproval') !!}', // Panggil rute indexApproval
        },
        columns: [
            { data: 'id', name: 'id', width: '5%'},
            { data: 'pr_number', name: 'pr_number' },
            { data: 'pr_date_formatted', name: 'pr_date' },
            { data: 'requester_name', name: 'requester.name' },
            { data: 'total_amount_formatted', name: 'total_amount', searchable: false, orderable: true },
            { data: 'status_badge', name: 'status', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%'},
        ],
    });
</script>
@endpush