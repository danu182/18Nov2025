
@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Tables</h3>
        <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('dashboard') }}">
            <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="">{{$title}}</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">{{$subtitle}}</a>
        </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md">

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Data Izin</h4>
                    <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-round ms-auto">
                        <i class="fa fa-plus"></i>
                        Tambah Izin
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="crudTable">
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
 <!-- Sweet Alert -->
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

    <script>
        // sweet alert success start
            // 1. Logic SweetAlert untuk Notifikasi Sukses
            @if(session('success'))
                // Pastikan Anda sudah memuat library SweetAlert2 di layout Anda.
                swal({
                    title: "Berhasil!",
                    text: "{{ session('success') }}", // Mengambil pesan dari controller
                    icon: "success",
                    buttons: {
                        confirm: {
                            text: "OK",
                            className: "btn btn-success"
                        }
                    },
                    timer: 4000, // Opsional: Notifikasi akan hilang setelah 4 detik
                    timerProgressBar: true
                });
            @endif
        // sweet alert success end

        // sweet alert delete start
        $(document).on('click', '.delete-confirmation', function() {
            var permissionId = $(this).data('id');
            var permissionName = $(this).data('name');
            var formId = '#deleteForm' + permissionId; // Ambil ID formulir yang akan disubmit

            // Panggil SweetAlert versi lama
            swal({ 
                title: 'Anda yakin?',
                text: "Anda akan menghapus izin: " + permissionName + ". Data yang sudah dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                // Menggunakan objek 'buttons' untuk YES/NO
                buttons: {
                    cancel: "Batalkan", // Tombol 'No'
                    confirm: { // Tombol 'Yes'
                        text: "Ya, Hapus!",
                        value: true,
                        className: "btn btn-danger", // Ganti warna sesuai tema Anda
                    }
                },
            }).then(function(isConfirm) { // SweetAlert versi lama menggunakan 'then(function(isConfirm))'
                if (isConfirm) {
                    // Jika dikonfirmasi, kirim (submit) formulir DELETE yang tersembunyi
                    $(formId).submit(); 
                }
            });
        });
        // sweet alert delete end



        // AJAX DataTable start
            var datatable = $('#crudTable').DataTable({
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
        // AJAX DataTable end

        </script>
@endpush