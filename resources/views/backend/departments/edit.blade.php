@extends('layouts.app')
@section('content')
<div class="container">
    <div class="page-inner">
        {{-- Header dan Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Edit Data</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('departments.index') }}">Departemen</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Edit {{ $department->name }}</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">Edit Departemen: **{{ $department->name }}**</h4></div>
                    <div class="card-body">
                        
                        {{-- Form menggunakan route update dan @method('PUT') --}}
                        <form action="{{ route('departments.update', $department->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Input NAME (Pre-filled) --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $department->name) }}" 
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Input CODE (Pre-filled) --}}
                            <div class="mb-3">
                                <label for="code" class="form-label">Kode Departemen</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('code') is-invalid @enderror" 
                                    id="code" 
                                    name="code" 
                                    value="{{ old('code', $department->code) }}" 
                                >
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
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