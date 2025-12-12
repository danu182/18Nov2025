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
                    <div class="card-header">
                        <h4 class="card-title">Detail Pengguna: **{{ $user->name }}**</h4>
                    </div>
                    <div class="card-body">
                        
                        {{-- Logika untuk menentukan URL Avatar --}}
                        @php
                            $avatarPath = $user->avatar 
                                ? asset('storage/' . $user->avatar)
                                : asset('assets/img/avatar/Header-avatar-01.jpg'); 
                        @endphp

                        <div class="row">
                            {{-- Kolom Kiri: Avatar dan Informasi Dasar --}}
                            <div class="col-md-5 text-center">
                                
                                <img src="{{ $avatarPath }}" 
                                     alt="{{ $user->name }}" 
                                     style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #eee;" 
                                     class="mb-3">

                                <h4 class="mb-1">**{{ $user->name }}**</h4>
                                <p class="text-muted">{{ $user->email }}</p>
                                
                                <hr>
                                
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm mb-2">
                                    <i class="fa fa-edit"></i> Edit Data
                                </a>
                            </div>

                            {{-- Kolom Kanan: Detail Data --}}
                            <div class="col-md-7">
                                
                                <h5>**👤 Informasi Akun**</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td style="width: 30%;">**ID Pengguna**</td>
                                        <td>: {{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <td>**Email**</td>
                                        <td>: {{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>**Bergabung Sejak**</td>
                                        <td>: {{ $user->created_at->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td>**Terakhir Diperbarui**</td>
                                        <td>: {{ $user->updated_at->format('d M Y H:i:s') }}</td>
                                    </tr>
                                </table>

                                <h5>**💼 Detail Organisasi & Peran**</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td>**Peran (Role)**</td>
                                        <td>: 
                                            {{-- Asumsi Spatie: ambil nama role pertama --}}
                                            @forelse ($user->getRoleNames() as $roleName)
                                                <span class="badge badge-info">{{ $roleName }}</span>
                                            @empty
                                                <span class="badge badge-warning">Belum ada Role</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>**Perusahaan**</td>
                                        <td>: **{{ $user->company->name ?? '— Tidak Ditentukan —' }}**</td>
                                    </tr>
                                    <tr>
                                        <td>**Departemen**</td>
                                        <td>: **{{ $user->department->name ?? '— Tidak Ditentukan —' }}**</td>
                                    </tr>
                                </table>

                                {{-- Jika ada Signature, tampilkan di sini --}}
                                @if($user->signature)
                                    <h5>**✍️ Tanda Tangan**</h5>
                                    <img src="{{ asset('storage/' . $user->signature) }}" alt="Tanda Tangan" style="max-width: 200px; border: 1px solid #ccc; padding: 5px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">
                            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection