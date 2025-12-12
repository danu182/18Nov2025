@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <h2>{{ $title }} - {{ $subtitle }}</h2>
    
    {{-- Aksi Persetujuan --}}
    @if ($purchaseRequest->status === 'Draft' && $purchaseRequest->current_approver_id === Auth::id())
        <div class="card my-4 border-warning">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Aksi Persetujuan (Anda adalah Approver Saat Ini)</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('purchase_requests.processApproval', $purchaseRequest->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan/Keterangan (Wajib diisi saat Reject, Opsional saat Approve)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" name="action" value="Approved" class="btn btn-success me-2" 
                            onclick="return confirm('Anda yakin ingin MENYETUJUI Purchase Request ini?')">
                        ✅ Approve
                    </button>

                    <button type="submit" name="action" value="Rejected" class="btn btn-danger" 
                            onclick="return confirm('Anda yakin ingin MENOLAK Purchase Request ini?\n\nCatatan wajib diisi saat menolak!')">
                        ❌ Reject
                    </button>
                    
                </form>
            </div>
        </div>
    @endif
    
    {{-- Tombol Aksi Utama --}}
    <div class="mb-4 d-flex gap-2">
        {{-- Tombol Cetak PR --}}
        <a href="{{ route('purchase_requests.print', $purchaseRequest->id) }}" target="_blank" class="btn btn-info">
            <i class="fas fa-print"></i> Cetak PR
        </a>

        {{-- INI ADALAH PENEMPATAN TOMBOL BUAT PO --}}
        @if ($purchaseRequest->status === 'Approved')
        {{-- <a href="{{ route('purchase_orders.show', ['pr_id' => $purchaseRequest->id]) }}"  --}}
        <a href="{{ route('purchase_orders.createFromPR', $purchaseRequest->id) }}" 
               class="btn btn-success">
                <i class="fas fa-file-invoice"></i> Buat Purchase Order
            </a>
        @endif
    </div>
    <hr>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Informasi Utama</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nomor PR:</strong> {{ $purchaseRequest->pr_number }}</p>
                    <p><strong>Tanggal PR:</strong> {{ \Carbon\Carbon::parse($purchaseRequest->pr_date)->format('d F Y') }}</p> 
                    <p>
                        <strong>Status:</strong> 
                        @php
                            $badgeClass = match ($purchaseRequest->status) {
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Pending' => 'warning',
                                'Draft' => 'secondary',
                                default => 'info',
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeClass }}">{{ $purchaseRequest->status }}</span>
                    </p>
                    <p><strong>Approver Saat Ini:</strong> {{ $purchaseRequest->currentApprover?->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Diminta Oleh:</strong> {{ $purchaseRequest->requester?->name ?? '-' }}</p>
                    <p><strong>Departemen:</strong> {{ $purchaseRequest->requester?->department?->name ?? '-' }}</p>
                    <p><strong>Perusahaan:</strong> {{ $purchaseRequest->requester?->company?->name ?? '-' }}</p>
                </div>
            </div>
            <hr>
            <p><strong>Tujuan Permintaan:</strong></p>
            <p>{{ $purchaseRequest->purpose ?? 'Tidak ada tujuan yang dicantumkan.' }}</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h4 class="mb-0">Detail Item Permintaan</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang/Jasa</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga Satuan (Est.)</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGrand = 0; @endphp
                        @forelse ($purchaseRequest->details as $index => $item)
                            @php $totalGrand += $item->subtotal; @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    
                                    {{-- Menampilkan Link Referensi --}}
                                    @if ($item->references->isNotEmpty())
                                        <div class="mt-2 p-2 border rounded bg-light">
                                            <small class="d-block mb-1 text-muted">Link Referensi:</small>
                                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                                @foreach ($item->references as $ref)
                                                    <li>
                                                        <a href="{{ $ref->url }}" target="_blank">
                                                            {{ $ref->description ?: $ref->url }} 
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->unit ?? '-' }}</td>
                                <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td> 
                                <td class="text-end">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada item yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">TOTAL KESELURUHAN</th>
                            <th class="text-end">Rp {{ number_format($purchaseRequest->total_amount ?? 0, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Riwayat Persetujuan (Approvals)</h4>
        </div>
        <div class="card-body">
            @if ($purchaseRequest->approvals->isNotEmpty())
                <ul class="list-group">
                    @foreach ($purchaseRequest->approvals->sortBy('level') as $approval) {{-- Sortir berdasarkan level --}}
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    {{ $approval->approver->name ?? 'Sistem' }} 
                                    ({{ $approval->level == 0 ? 'Submitter' : 'Level ' . $approval->level }})
                                </div>
                                
                                @php
                                    $actionClass = match ($approval->action) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Submitted' => 'primary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $actionClass }} me-2">{{ $approval->action }}</span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($approval->action_at)->format('d M Y H:i') }}</small>
                                
                                @if ($approval->notes)
                                    <br><small class="text-dark">Catatan: {{ $approval->notes }}</small>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>Belum ada riwayat persetujuan.</p>
            @endif
        </div>
    </div>

    <div class="mt-4 mb-5">
        <a href="{{ route('purchase_requests.index') }}" class="btn btn-secondary">← Kembali ke Daftar PR</a>
        @if ($purchaseRequest->status == 'Draft')
            <a href="{{ route('purchase_requests.edit', $purchaseRequest->id) }}" class="btn btn-warning">✏️ Edit Permintaan</a>
        @endif
    </div>

</div>
@endsection