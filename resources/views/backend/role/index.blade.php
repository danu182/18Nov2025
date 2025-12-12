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
                            <h4 class="card-title">Data Role</h4>
                            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Tambah Role
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- Ubah id tabel menjadi 'roleTable' --}}
                            <table class="table table-hover" id="roleTable">
                                <thead>
                                    <tr>
                                        <th scope="col">id</th>
                                        <th scope="col">name</th>
                                        <th scope="col">guard_name</th>
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
    // 1. Notifikasi Sukses
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

    // 2. Inisialisasi AJAX DataTable untuk ROLE
    var datatable = $('#roleTable').DataTable({ // <-- Gunakan ID #roleTable
        ajax: {
            url: '{!! url()->current() !!}',
        },
        columns: [
            { data: 'id', name: 'id', width: '5%'},
            { data: 'name', name: 'name' },
            { data: 'guard_name', name: 'guard_name' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                width: '25%'
            },
        ],
    });

    // 3. Logika SweetAlert Delete (DISESUAIKAN)
    $(document).on('click', '.delete-confirmation', function() {
        var itemId = $(this).data('id');
        var itemName = $(this).data('name');
        var itemType = $(this).data('type'); // Tambahkan ini
        
        var formId = '#deleteRoleForm' + itemId; // <-- Sesuaikan ID form DELETE

        swal({
            title: 'Anda yakin?',
            text: "Anda akan menghapus role: " + itemName + ". Data yang sudah dihapus tidak dapat dikembalikan!",
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