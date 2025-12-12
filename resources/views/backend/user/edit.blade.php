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
                    <div class="card-header"><h4 class="card-title">{{ $subtitle }} - {{ $user->name }}</h4></div>
                    <div class="card-body">
                        
                        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT') {{-- PENTING: Menggunakan method PUT untuk pembaruan --}}

                            {{-- Path Default Gambar (untuk handling jika belum ada) --}}
                            @php
                                $defaultAvatar = asset('path/to/default/avatar.png');
                                $defaultSignature = asset('path/to/default/signature_placeholder.png');
                                
                                $currentAvatar = $user->avatar ? asset('storage/' . $user->avatar) : $defaultAvatar;
                                $currentSignature = $user->signature ? asset('storage/' . $user->signature) : $defaultSignature;
                            @endphp

                            {{-- Input Upload Avatar --}}
                            <div class="form-group">
                                <label for="avatar">Foto Profil (Avatar)</label>
                                <input type="file" name="avatar" class="form-control-file @error('avatar') is-invalid @enderror" id="avatar">
                                <small class="form-text text-muted">Abaikan jika tidak ingin diubah. Maksimal 2MB, format JPG/PNG.</small>
                                
                                @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                                {{-- Elemen Gambar untuk Preview (Menampilkan yang sudah ada) --}}
                                <div class="mt-2">
                                    <img id="avatar-preview" src="{{ $currentAvatar }}" 
                                        alt="Pratinjau Avatar" 
                                        style="max-width: 150px; max-height: 150px; border: 1px solid #ccc; padding: 5px; display: block;">
                                </div>
                            </div>
                            
                            <hr>

                            {{-- Input Upload Tanda Tangan/Signature --}}
                            <div class="form-group mb-4">
                                <label for="signature">Tanda Tangan Digital (Signature)</label>
                                <input type="file" name="signature" class="form-control-file @error('signature') is-invalid @enderror" id="signature">
                                <small class="form-text text-muted">Abaikan jika tidak ingin diubah. Maksimal 1MB, format PNG transparan disarankan.</small>
                                
                                @error('signature')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                
                                {{-- Elemen Gambar untuk Preview Signature (Menampilkan yang sudah ada) --}}
                                <div class="mt-2">
                                    <img id="signature-preview" 
                                        src="{{ $currentSignature }}" 
                                        alt="Pratinjau Tanda Tangan" 
                                        style="max-width: 250px; max-height: 100px; border: 1px dashed #ccc; padding: 5px; display: block; background-color: #f9f9f9;">
                                </div>
                            </div>

                            <hr>

                            {{-- 1. Field NAMA --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $user->name) }}" {{-- Pre-filled dengan data user --}}
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
                                    value="{{ old('email', $user->email) }}" {{-- Pre-filled dengan data user --}}
                                    required
                                >
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            {{-- 3. Field PASSWORD (Opsional) --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                    <small class="form-text text-muted">Isi hanya jika ingin mengganti password.</small>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                {{-- 4. Dropdown ROLE --}}
                                
                                <div class="col-md-4 mb-3">
                                    {{-- ... label ... --}}
                                    <label for="role_id" class="form-label">Role</label>
                                    <select class="form-control @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                        <option value="">-- Pilih Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                {{ 
                                                    // Bandingkan dengan old(), lalu fallback ke $currentRoleId dari Controller
                                                    (old('role_id') ?? $currentRoleId) == $role->id 
                                                    ? 'selected' 
                                                    : '' 
                                                }}>
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
                                            <option value="{{ $company->id }}" 
                                                {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
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
                                            <option value="{{ $department->id }}" 
                                                {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-sync"></i> Perbarui Pengguna</button>
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
<script>
    // 1. Ambil elemen input file dan elemen gambar preview untuk AVATAR
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');

    // 2. Ambil elemen input file dan elemen gambar preview untuk SIGNATURE
    const signatureInput = document.getElementById('signature');
    const signaturePreview = document.getElementById('signature-preview');
    
    // Fungsi umum untuk preview file gambar
    function setupFilePreview(inputElement, previewElement, defaultPath) {
        inputElement.addEventListener('change', function(event) {
            
            if (event.target.files && event.target.files[0]) {
                const file = event.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewElement.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                // Jika input dikosongkan, kembalikan ke gambar yang sudah ada/default
                previewElement.src = defaultPath; 
            }
        });
    }

    // Panggil fungsi setup untuk Avatar
    // Menggunakan path yang sudah di-define di PHP
    setupFilePreview(avatarInput, avatarPreview, "{{ $currentAvatar }}");
    
    // Panggil fungsi setup untuk Signature
    setupFilePreview(signatureInput, signaturePreview, "{{ $currentSignature }}");

</script>
@endpush