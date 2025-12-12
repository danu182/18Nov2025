@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Master Data</h3>
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
                            <h4 class="card-title">Data Departemen</h4>
                            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Tambah Departemen
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- ID Tabel untuk DataTables --}}
                            <table class="table table-hover" id="departmentTable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Code</th>
                                        <th scope="col">Name</th>
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
    // 1. Notifikasi Sukses (SweetAlert Classic)
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

    // 2. Notifikasi Error
    @if(session('error'))
        swal({ 
            title: "Gagal!",
            text: "{{ session('error') }}", 
            icon: "error",
            buttons: {
                confirm: {
                    text: "OK",
                    className: "btn btn-danger"
                }
            },
        });
    @endif

    // 3. Inisialisasi AJAX DataTable untuk DEPARTMENT
    var datatable = $('#departmentTable').DataTable({ // <-- ID #departmentTable
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url()->current() !!}',
        },
        columns: [
            { data: 'id', name: 'id', width: '5%'},
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                width: '20%'
            },
        ],
    });

    // 4. Logika SweetAlert Delete
    $(document).on('click', '.delete-confirmation', function() {
        var itemId = $(this).data('id');
        var itemName = $(this).data('name');
        
        // Asumsi form ID Anda untuk delete Department adalah #deleteDepartmentForm
        var formId = '#deleteDepartmentForm' + itemId; 

        swal({
            title: 'Anda yakin?',
            text: "Anda akan menghapus departemen: " + itemName + ". Data yang sudah dihapus tidak dapat dikembalikan!",
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