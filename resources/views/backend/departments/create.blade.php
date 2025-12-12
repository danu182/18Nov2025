@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-inner">
        {{-- Header dan Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Master Data</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('departments.index') }}">Departemen</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Tambah Departemen</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">Tambah Departemen Baru</h4></div>
                    <div class="card-body">
                        
                        <form action="{{ route('departments.store') }}" method="POST">
                            @csrf

                            {{-- Input NAME --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    placeholder="Contoh: Keuangan"
                                    value="{{ old('name') }}" 
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Input CODE --}}
                            <div class="mb-3">
                                <label for="code" class="form-label">Kode Departemen</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('code') is-invalid @enderror" 
                                    id="code" 
                                    name="code" 
                                    placeholder="Contoh: FIN"
                                    value="{{ old('code') }}" 
                                >
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Departemen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection