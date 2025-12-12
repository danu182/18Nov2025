@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        {{-- Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Detail Perusahaan</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('companies.index') }}">Perusahaan</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Detail</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Detail Perusahaan: **{{ $company->name }}**</h4>
                    </div>
                    <div class="card-body">
                        
                        <dl class="row">
                            {{-- CODE --}}
                            <dt class="col-sm-3">Kode Perusahaan:</dt>
                            <dd class="col-sm-9">{{ $company->code }}</dd>

                            {{-- NAME --}}
                            <dt class="col-sm-3">Nama Perusahaan:</dt>
                            <dd class="col-sm-9">{{ $company->name }}</dd>

                            <hr class="my-3">

                            {{-- EMAIL --}}
                            <dt class="col-sm-3">Email:</dt>
                            <dd class="col-sm-9">{{ $company->email ?? '-' }}</dd>

                            {{-- PHONE --}}
                            <dt class="col-sm-3">Telepon:</dt>
                            <dd class="col-sm-9">{{ $company->phone ?? '-' }}</dd>

                            {{-- PIC --}}
                            <dt class="col-sm-3">Person In Charge (PIC):</dt>
                            <dd class="col-sm-9">{{ $company->pic ?? '-' }}</dd>
                            
                            <hr class="my-3">

                            {{-- ADDRESS --}}
                            <dt class="col-sm-3">Alamat:</dt>
                            <dd class="col-sm-9">{{ $company->address ?? '-' }}</dd>
                        </dl>

                        <div class="mt-4">
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary me-2">
                                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                            <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-primary">
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