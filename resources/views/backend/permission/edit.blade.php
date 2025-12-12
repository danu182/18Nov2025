@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        {{-- Breadcrumbs dan Page Header --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Permissions</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}"><i class="icon-home"></i></a>
                </li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Edit Izin</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Izin: {{ $permission->name }}</h4>
                    </div>
                    <div class="card-body">
                        
                        {{-- Formulir untuk Update --}}
                        <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
                            @csrf
                            @method('PUT') {{-- PENTING: Untuk menggunakan metode HTTP PUT/PATCH --}}
                            
                            {{-- Input untuk Nama Izin --}}
                            <div class="form-group">
                                <label for="name">Nama Izin</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    placeholder="Contoh: user_create" 
                                    value="{{ old('name', $permission->name) }}" {{-- Pre-fill data lama --}}
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Input Guard Name --}}
                            <div class="form-group">
                                <label for="guard_name">Guard Name</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('guard_name') is-invalid @enderror" 
                                    id="guard_name" 
                                    name="guard_name" 
                                    placeholder="Default: web" 
                                    value="{{ old('guard_name', $permission->guard_name) }}" {{-- Pre-fill data lama --}}
                                >
                                @error('guard_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('permissions.index') }}" class="btn btn-secondary me-2">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection