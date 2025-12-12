@extends('layouts.app') 
{{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container-fluid pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🧾 {{ $title ?? 'Detail Purchase Order' }}</h2>
        <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar PO
        </a>
    </div>

    <hr>

    {{-- Header dan Status PO --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="m-0">PO Number: **{{ $po->po_number }}**</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Status:</strong> 
                        {{-- Menggunakan statusMap dari Controller --}}
                        <span class="badge bg-{{ $statusMap[$po->status] ?? 'secondary' }} fs-6">{{ $po->status }}</span>
                    </p>
                    <p><strong>Tanggal PO:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</p>
                    <p><strong>Batas Pengiriman (Delivery):</strong> 
                        {{ $po->required_delivery_date ? \Carbon\Carbon::parse($po->required_delivery_date)->format('d F Y') : '-' }}
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="btn btn-sm btn-outline-info me-2"><i class="fas fa-print"></i> Cetak PO</a>
                    @if ($po->status == 'Draft')
                        <a href="{{ route('purchase_orders.edit', $po->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                    @endif
                    {{-- Tambahkan tombol aksi (Approve/Reject) jika ada logic approval --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi Umum (Vendor dan PR) --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0">Informasi Vendor</h6>
                </div>
                <div class="card-body">
                    <p><strong>Nama Vendor:</strong> {{ $po->vendor->name ?? '-' }}</p>
                    <p><strong>Alamat:</strong> {{ $po->vendor->address ?? '-' }}</p>
                    <p><strong>Kontak:</strong> {{ $po->vendor->phone ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0">Dokumen Pendukung</h6>
                </div>
                <div class="card-body">
                    <p><strong>Dibuat Oleh:</strong> {{ $po->creator->name ?? '-' }}</p>
                    <p><strong>PR Number Asal:</strong> 
                        @if($po->purchaseRequest)
                            <a href="{{ route('purchase_requests.show', $po->purchaseRequest->id) }}">
                                **{{ $po->purchaseRequest->pr_number }}**
                            </a>
                        @else
                            -
                        @endif
                    </p>
                    <p><strong>PR Dibuat Oleh:</strong> {{ $po->purchaseRequest->requester->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Item PO (Wajib Ada) --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white">
            <h6 class="m-0">Daftar Item PO ({{ $po->details->count() }} Item)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Harga Satuan</th>
                            <th>Total Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($po->details as $detail)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $detail->item_name }}</td>
                                <td>{{ number_format($detail->quantity, 0, ',', '.') }}</td>
                                <td>{{ $detail->unit }}</td>
                                <td>{{ $po->currency ?? 'IDR' }} {{ number_format($detail->unit_price, 2, ',', '.') }}</td>
                                <td>**{{ $po->currency ?? 'IDR' }} {{ number_format($detail->unit_price * $detail->quantity, 2, ',', '.') }}**</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Total Keseluruhan (Termasuk PPN/Diskoun jika ada):</strong></td>
                            <td>**{{ $po->currency ?? 'IDR' }} {{ number_format($po->total_amount, 2, ',', '.') }}**</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Log Persetujuan (Opsional, tapi penting untuk sistem approval) --}}
    @if ($po->purchaseApprovalLogs->isNotEmpty())
        <div class="card shadow mb-4">
            <div class="card-header bg-warning">
                <h6 class="m-0">Riwayat Persetujuan</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach ($po->purchaseApprovalLogs as $log)
                        <li class="list-group-item">
                            Pada {{ \Carbon\Carbon::parse($log->action_date)->format('d M Y H:i') }}, **{{ $log->user->name ?? 'User Tidak Dikenal' }}**
                            melakukan aksi: 
                            <span class="badge bg-{{ $statusMap[$log->action] ?? 'secondary' }}">{{ $log->action }}</span>
                            @if ($log->notes)
                                <br><small class="text-muted">Catatan: "{{ $log->notes }}"</small>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection