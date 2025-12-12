@extends('layouts.app')

@section('content')

<div class="container">
    <div class="page-inner">
        {{-- Breadcrumbs --}}
        <div class="page-header">
            <h3 class="fw-bold mb-3">Detail Role</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home"><a href="{{ route('dashboard') }}"><i class="icon-home"></i></a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Detail Role</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4 class="card-title">Detail Role: **{{ $role->name }}**</h4></div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Role:</label>
                            <p class="form-control-static">{{ $role->name }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Guard Name:</label>
                            <p class="form-control-static">{{ $role->guard_name }}</p>
                        </div>

                        <hr>

                        <label class="form-label fw-bold">Daftar Izin (Permissions):</label>
                        @if($role->permissions->isEmpty())
                            <div class="alert alert-warning">Role ini tidak memiliki Izin yang dikaitkan.</div>
                        @else
                            <ul class="list-group">
                                @foreach ($role->permissions as $permission)
                                    <li class="list-group-item">
                                        <i class="fa fa-check text-success me-2"></i> {{ $permission->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary me-2">
                                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection