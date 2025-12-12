@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        {{-- Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Roles</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Edit Role</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">Edit Role: **{{ $role->name }}**</h4></div>
                    <div class="card-body">
                        
                        {{-- Formulir menggunakan metode POST dengan directive @method('PUT') --}}
                        <form action="{{ route('roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            {{-- Input Nama Role (Pre-filled) --}}
                            <div class="form-group">
                                <label for="name">Nama Role</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    placeholder="Contoh: administrator" 
                                    value="{{ old('name', $role->name) }}" // <-- Pre-fill data Role
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Input Permissions (Checkboxes) --}}
                            <div class="form-group">
                                <label for="permissions">Pilih Izin (Permissions)</label>
                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="permissions[]" 
                                                    value="{{ $permission->name }}" 
                                                    id="check-{{ $permission->id }}"
                                                    
                                                    // Logic KRITIS untuk menandai Permission yang sudah dimiliki
                                                    {{ 
                                                        (in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '') 
                                                    }}
                                                >
                                                <label class="form-check-label" for="check-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach

                                    @error('permissions')
                                        <div class="text-danger mt-2">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection