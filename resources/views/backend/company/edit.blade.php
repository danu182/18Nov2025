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
                <li class="nav-item"><a href="{{ route('companies.index') }}">Perusahaan</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Edit {{ $company->name }}</a></li>
            </ul>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">Edit Perusahaan: **{{ $company->name }}**</h4></div>
                    <div class="card-body">
                        
                        {{-- Form menggunakan route update dan @method('PUT') --}}
                        <form action="{{ route('companies.update', $company->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                {{-- Input CODE (Pre-filled) --}}
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">Kode Perusahaan</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('code') is-invalid @enderror" 
                                        id="code" 
                                        name="code" 
                                        value="{{ old('code', $company->code) }}" 
                                        required
                                    >
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                {{-- Input NAME (Pre-filled) --}}
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Perusahaan</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        id="name" 
                                        name="name" 
                                        value="{{ old('name', $company->name) }}" 
                                        required
                                    >
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                {{-- Input EMAIL (Pre-filled) --}}
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email', $company->email) }}"
                                    >
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                {{-- Input PHONE (Pre-filled) --}}
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        id="phone" 
                                        name="phone" 
                                        value="{{ old('phone', $company->phone) }}"
                                    >
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Input PIC (Pre-filled) --}}
                            <div class="mb-3">
                                <label for="pic" class="form-label">Person In Charge (PIC)</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('pic') is-invalid @enderror" 
                                    id="pic" 
                                    name="pic" 
                                    value="{{ old('pic', $company->pic) }}"
                                >
                                @error('pic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Input ADDRESS (Pre-filled) --}}
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea 
                                    class="form-control @error('address') is-invalid @enderror" 
                                    id="address" 
                                    name="address"
                                >{{ old('address', $company->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('companies.index') }}" class="btn btn-secondary me-2"><i class="fa fa-arrow-left"></i> Kembali</a>
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