@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Tables</h3>
            {{-- Breadcrumbs --}}
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}"><i class="icon-home"></i></a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="">{{$title}}</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">{{$subtitle}}</a></li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md">

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">Data Perusahaan</h4>
                            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Tambah Perusahaan
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- ID Tabel untuk DataTables --}}
                            <table class="table table-hover" id="companyTable">
                                <thead>
                                    <tr>
                                        <th scope="col">id</th>
                                        <th scope="col">Code</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">PIC</th>
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

@push('script-bawah')

<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

<script>
    // 1. Notifikasi Sukses (Menggunakan SweetAlert Classic)
    @if(session('success'))
        swal({ 
            title: "Berhasil!",
            text: "{{ session('success') }}", 
            icon: "success",
            buttons: {
                confirm: {
                    text: "OK",
                    className: "btn btn-success"
                }
            },
            timer: 4000, 
            timerProgressBar: true
        });
    @endif

    // 2. Inisialisasi AJAX DataTable untuk COMPANY
    var datatable = $('#companyTable').DataTable({ // <-- Gunakan ID #companyTable
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url()->current() !!}',
        },
        columns: [
            // Kolom data harus cocok dengan select di CompanyController
            { data: 'id', name: 'id', width: '5%'},
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'pic', name: 'pic' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                width: '20%'
            },
        ],
    });

    // 3. Logika SweetAlert Delete (Jika Anda mengimplementasikan delete)
    $(document).on('click', '.delete-confirmation', function() {
        var itemId = $(this).data('id');
        var itemName = $(this).data('name');
        
        // Asumsi form ID Anda untuk delete Company adalah #deleteCompanyForm
        var formId = '#deleteCompanyForm' + itemId; 

        swal({
            title: 'Anda yakin?',
            text: "Anda akan menghapus perusahaan: " + itemName + ". Data yang sudah dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            buttons: {
                cancel: "Batalkan",
                confirm: {
                    text: "Ya, Hapus!",
                    value: true,
                    className: "btn btn-danger", 
                }
            },
        }).then(function(isConfirm) {
            if (isConfirm) {
                $(formId).submit(); 
            }
        });
    });
</script>
@endpush