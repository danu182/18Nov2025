@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">{{ $title }}</h3>
            {{-- Breadcrumbs --}}
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">{{ $title }}</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">{{ $subtitle }}</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md">

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">{{ $subtitle }}</h4>
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-round ms-auto">
                                <i class="fa fa-plus"></i>
                                Tambah Pengguna
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="userTable">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Avatar</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Email</th>
                                        {{-- <th scope="col">Nama</th> --}}
                                        <th scope="col">Role</th>
                                        <th scope="col">Perusahaan</th>
                                        <th scope="col">Departemen</th>
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
    // 1. Notifikasi Sukses/Error (Diambil dari session Controller)
    @if(session('success'))
        swal({ 
            title: "Berhasil!",
            text: "{!! session('success') !!}", // Menggunakan !! !! agar markdown bold (**) dibaca
            icon: "success",
            buttons: { confirm: { text: "OK", className: "btn btn-success" } },
            timer: 4000, timerProgressBar: true
        });
    @endif

    @if(session('error'))
        swal({ 
            title: "Gagal!",
            text: "{!! session('error') !!}", 
            icon: "error",
            buttons: { confirm: { text: "OK", className: "btn btn-danger" } },
        });
    @endif

    // 2. Inisialisasi AJAX DataTable untuk USER
    $('#userTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! url()->current() !!}',
        },
        columns: [
            { data: 'id', name: 'id', width: '5%'},
            { data: 'avatar_display', name: 'avatar_display', orderable: false, searchable: false, width: '5%'}, // <-- Kolom Avatar
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            // Kolom dari addColumn di Controller
            { data: 'role_name', name: 'role_name', orderable: false, searchable: false }, 
            { data: 'company_name', name: 'company.name' }, // Searchable menggunakan dot notation
            { data: 'department_name', name: 'department.name' }, // Searchable menggunakan dot notation
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                width: '20%'
            },
        ],
    });

    // 3. Logika SweetAlert Delete
    $(document).on('click', '.delete-confirmation', function() {
        var itemId = $(this).data('id');
        var itemName = $(this).data('name');
        var formId = '#deleteUserForm' + itemId; 

        swal({
            title: 'Anda yakin?',
            text: "Anda akan menghapus pengguna: " + itemName + ". Data yang sudah dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            buttons: {
                cancel: "Batalkan",
                confirm: { text: "Ya, Hapus!", value: true, className: "btn btn-danger", }
            },
        }).then(function(isConfirm) {
            if (isConfirm) {
                $(formId).submit(); 
            }
        });
    });
</script>
@endpush