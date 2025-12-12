@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        {{-- Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Detail Departemen</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('departments.index') }}">Departemen</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Detail</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Detail Departemen: **{{ $department->name }}**</h4>
                    </div>
                    <div class="card-body">
                        
                        <dl class="row">
                            {{-- NAME --}}
                            <dt class="col-sm-4">Nama Departemen:</dt>
                            <dd class="col-sm-8">{{ $department->name }}</dd>

                            {{-- CODE --}}
                            <dt class="col-sm-4">Kode Departemen:</dt>
                            <dd class="col-sm-8">{{ $department->code ?? '-' }}</dd>
                            
                            {{-- Jika Anda ingin menampilkan tanggal dibuat/diupdate --}}
                            <hr class="my-3">
                            <dt class="col-sm-4">Dibuat Pada:</dt>
                            <dd class="col-sm-8">{{ $department->created_at->format('d M Y, H:i') }}</dd>
                        </dl>

                        <div class="mt-4">
                            <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2">
                                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                            <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Edit Data
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection