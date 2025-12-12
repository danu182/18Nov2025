@extends('layouts.app')
@section('content')
<div class="container">
    <div class="page-inner">
        {{-- Header dan Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">{{ $title }}</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">{{ $subtitle }}</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">{{ $subtitle }}</h4></div>
                    <div class="card-body">
                        
                        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf


                            {{-- Input Upload Avatar --}}
                            <div class="form-group">
                                <label for="avatar">Foto Profil (Avatar)</label>
                                <input type="file" name="avatar" class="form-control-file" id="avatar">
                                <small class="form-text text-muted">Maksimal 2MB, format JPG/PNG.</small>
                                
                                {{-- Elemen Gambar untuk Preview --}}
                                <div class="mt-2">
                                    <img id="avatar-preview" src="{{ asset('path/to/default/avatar.png') }}" 
                                        alt="Pratinjau Avatar" 
                                        style="max-width: 150px; max-height: 150px; border: 1px solid #ccc; padding: 5px; display: block;">
                                </div>
                            </div>

                            {{-- 1. Field NAMA --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}" 
                                    required
                                >
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 2. Field EMAIL --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input 
                                    type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required
                                >
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            {{-- 3. Field PASSWORD --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                    {{-- Laravel otomatis mencocokkan field ini dengan aturan 'confirmed' pada validasi password --}}
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            {{-- Input Upload Tanda Tangan/Signature --}}
                            <div class="form-group mb-4">
                                <label for="signature">Tanda Tangan Digital (Signature)</label>
                                <input type="file" name="signature" class="form-control-file @error('signature') is-invalid @enderror" id="signature">
                                <small class="form-text text-muted">Maksimal 1MB, format PNG transparan disarankan.</small>
                                
                                {{-- Elemen Gambar untuk Preview Signature --}}
                                <div class="mt-2">
                                    {{-- Atur src ke placeholder default --}}
                                    <img id="signature-preview" 
                                        src="{{ asset('path/to/default/signature_placeholder.png') }}" 
                                        alt="Pratinjau Tanda Tangan" 
                                        style="max-width: 250px; max-height: 100px; border: 1px dashed #ccc; padding: 5px; display: block; background-color: #f9f9f9;">
                                </div>
                                @error('signature')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                {{-- 4. Dropdown ROLE --}}
                                <div class="col-md-4 mb-3">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                {{-- 5. Dropdown COMPANY --}}
                                <div class="col-md-4 mb-3">
                                    <label for="company_id" class="form-label">Perusahaan</label>
                                    <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                        <option value="">-- Tidak Ada (Opsional) --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 6. Dropdown DEPARTMENT --}}
                                <div class="col-md-4 mb-3">
                                    <label for="department_id" class="form-label">Departemen</label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                        <option value="">-- Tidak Ada (Opsional) --</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Pengguna</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-bawah')
{{-- <script>
    // Ambil elemen input file dan elemen gambar preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    // Tambahkan event listener untuk mendeteksi perubahan pada input file
    avatarInput.addEventListener('change', function(event) {
        // Pastikan ada file yang dipilih
        if (event.target.files && event.target.files[0]) {
            const file = event.target.files[0];
            
            // Inisialisasi FileReader
            const reader = new FileReader();

            // Ketika file selesai dibaca
            reader.onload = function(e) {
                // Atur sumber gambar preview ke data URL yang dibaca
                avatarPreview.src = e.target.result;
            }

            // Baca file sebagai Data URL
            reader.readAsDataURL(file);
        } else {
            // Jika pengguna membatalkan atau tidak memilih file, 
            // kembalikan gambar ke default (opsional)
            // avatarPreview.src = "{{ asset('path/to/default/avatar.png') }}";
        }
    });
</script> --}}

<script>
    // 1. Ambil elemen input file dan elemen gambar preview untuk AVATAR
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    // 2. Ambil elemen input file dan elemen gambar preview untuk SIGNATURE
    const signatureInput = document.getElementById('signature');
    const signaturePreview = document.getElementById('signature-preview');
    
    // Fungsi umum untuk preview file gambar (dapat digunakan untuk Avatar dan Signature)
    function setupFilePreview(inputElement, previewElement) {
        // Tambahkan event listener untuk mendeteksi perubahan pada input file
        inputElement.addEventListener('change', function(event) {
            
            // Pastikan ada file yang dipilih
            if (event.target.files && event.target.files[0]) {
                const file = event.target.files[0];
                
                // Inisialisasi FileReader
                const reader = new FileReader();

                // Ketika file selesai dibaca
                reader.onload = function(e) {
                    // Atur sumber gambar preview ke data URL yang dibaca
                    previewElement.src = e.target.result;
                }

                // Baca file sebagai Data URL
                reader.readAsDataURL(file);
            } else {
                // Opsional: Jika input dikosongkan, kembalikan ke gambar default
                // previewElement.src = "{{ asset('path/to/default/placeholder.png') }}";
            }
        });
    }

    // Panggil fungsi setup untuk Avatar
    setupFilePreview(avatarInput, avatarPreview);
    
    // Panggil fungsi setup untuk Signature
    setupFilePreview(signatureInput, signaturePreview);

</script>

@endpush