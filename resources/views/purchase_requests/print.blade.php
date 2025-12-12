<!DOCTYPE html>
<html>
<head>
    <title>CETAK PR - {{ $purchaseRequest->pr_number }}</title>
    <!-- Referensi ke bootstrap-minimal.css dihapus karena error 404 -->
    <style>
        /* CSS Khusus Cetak */
        @media print {
            body { 
                font-size: 10pt; 
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        h1, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 10px;
            vertical-align: top;
            font-size: 9pt;
        }
        .header-table td {
            border: none !important; /* Hilangkan border pada tabel header */
        }
        .header-table td:first-child {
            font-weight: bold;
            width: 25%;
        }
        .signature-table {
            margin-top: 40px;
            width: 100%;
        }
        .signature-table th, .signature-table td {
            /* Menghapus border untuk tabel tanda tangan agar tampilan lebih rapi di print */
            border: 1px solid #000; /* Mengembalikan border untuk memperjelas kolom tanda tangan */
            padding: 0 10px;
        }
        .signature-table th {
            border-bottom: none !important;
        }
        .signature-table td {
            height: 100px; /* Ruang untuk tanda tangan */
            vertical-align: top; /* Posisikan nama di bawah */
            text-align: center;
            padding-bottom: 5px;
        }
        .signature-box {
            position: relative;
            height: 70px; /* Ruang di dalam kotak untuk tanda tangan */
            margin-bottom: 10px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
        }
        /* Style untuk gambar tanda tangan */
        .signature-image {
            position: absolute;
            top: 0; 
            left: 50%;
            transform: translateX(-50%);
            max-width: 90%;
            max-height: 70px; /* Sesuaikan dengan tinggi signature-box */
            object-fit: contain; 
        }
        .small-text {
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <h3 style="margin-bottom: 0;">{{ $purchaseRequest->requester->company->name ?? 'PT PANDU DEWANATA' }}</h3>
        <h1 style="margin-top: 5px; border-bottom: 2px solid #000; padding-bottom: 10px;">BANK PAYMENT REQUEST FORM</h1>
        
        <table class="header-table">
            <tr>
                <td>Requester:</td>
                <td>{{ $purchaseRequest->requester->name ?? '-' }}</td>
                <td>Title:</td>
                <td>{{ $purchaseRequest->purpose ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>{{ $purchaseRequest->requester->department->name ?? 'N/A' }} / {{ $purchaseRequest->requester->company->name ?? 'N/A' }}</td>
                <td>Request Date:</td>
                <td>{{ $purchaseRequest->pr_date ? $purchaseRequest->pr_date->format('d-M-y') : '-' }}</td>
            </tr>
            <tr>
                <td>Nomor PR:</td>
                <td>{{ $purchaseRequest->pr_number }}</td>
                <td>Payment Due Date:</td>
                <td>{{ $purchaseRequest->due_date ?? 'N/A' }}</td> 
                </tr>
        </table>
        
        <table class="table-detail">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Invoices No.</th> 
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%;">Reference</th>
                    <th style="width: 15%; text-align: right;">Total Amount (Rp)</th>
                    <th style="width: 10%;">Account No</th> </tr>
            </thead>
            <tbody>
                @foreach ($purchaseRequest->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->invoice_no ?? $detail->item_name }}</td> 
                    <td>{{ $detail->item_name }}</td>
                    <td>
                        @foreach ($detail->references as $ref)
                            <a href="{{ $ref->url }}" class="small-text" target="_blank">{{ $ref->description ?? 'Link' }}</a><br>
                        @endforeach
                    </td>
                    <td style="text-align: right;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    <td>{{ $detail->account_no ?? 'N/A' }}</td> </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: right;">**Total Amount**</td>
                    <td style="text-align: right;">**{{ number_format($purchaseRequest->total_amount, 0, ',', '.') }}**</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        @php
            // Ambil data approval berdasarkan level
            $prepared = $purchaseRequest->approvals->where('level', 0)->first(); 
            $checked = $purchaseRequest->approvals->where('level', 1)->where('action', 'Approved')->first(); 
            $approved = $purchaseRequest->approvals->where('level', 2)->where('action', 'Approved')->first(); 

            // Definisikan siapa yang menandatangani:
            // Prepared by: Biasanya Requester
            $preparedUser = $prepared ? $prepared->approver : $purchaseRequest->requester;
            // Checked by: Approver Level 1
            $checkedUser = $checked ? $checked->approver : null;
            // Approved by: Approver Level 2
            $approvedUser = $approved ? $approved->approver : null;
        @endphp

        <!-- BAGIAN DEBUGGING SEMENTARA (HANYA MUNCUL DI LAYAR, TIDAK DICETAK) -->
        {{-- <div class="no-print" style="margin-top: 20px; border: 1px dashed red; padding: 10px; background-color: #ffebeb; font-size: 10pt;">
            <p style="font-weight: bold; color: red; margin-bottom: 5px;">DEBUG SIGNATURE PATHS</p>
            
            <p style="margin-bottom: 2px;">
                <span style="font-weight: bold;">Prepared User ({{ $preparedUser->name ?? 'N/A' }}):</span> {{ $preparedUser->signature ?? 'NULL' }} 
                @if ($preparedUser && $preparedUser->signature)
                    (<a href="{{ asset('storage/' . $preparedUser->signature) }}" target="_blank" style="color: blue;">Cek URL</a>)
                @endif
            </p>

            <p style="margin-bottom: 2px;">
                <span style="font-weight: bold;">Checked User ({{ $checkedUser->name ?? 'N/A' }}):</span> {{ $checkedUser->signature ?? 'NULL' }} 
                @if ($checkedUser && $checkedUser->signature)
                    (<a href="{{ asset('storage/' . $checkedUser->signature) }}" target="_blank" style="color: blue;">Cek URL</a>)
                @endif
            </p>

            <p style="margin-bottom: 2px;">
                <span style="font-weight: bold;">Approved User ({{ $approvedUser->name ?? 'N/A' }}):</span> {{ $approvedUser->signature ?? 'NULL' }} 
                @if ($approvedUser && $approvedUser->signature)
                    (<a href="{{ asset('storage/' . $approvedUser->signature) }}" target="_blank" style="color: blue;">Cek URL</a>)
                @endif
            </p>

            <p style="margin-top: 10px; color: #555;">Jika tautan 'Cek URL' menghasilkan Error 404, file signature tidak ditemukan. Pastikan Anda sudah menjalankan 'php artisan storage:link' dan path di database benar.</p>
        </div> --}}
        <!-- END DEBUGGING -->

        <table class="signature-table">
            <thead>
                <tr>
                    <th style="width: 33%; border-right: 1px solid #000;">Prepared by:</th>
                    <th style="width: 33%; border-right: 1px solid #000;">Checked by:</th>
                    <th style="width: 34%;">Approved by:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border-right: 1px solid #000;">
                        <br>
                        <div class="signature-box">
                            @if ($preparedUser && $preparedUser->signature) 
                                {{-- Jika user memiliki signature, tampilkan gambarnya --}}
                                <img src="{{ asset('storage/' . $preparedUser->signature) }}" alt="Signature Prepared" class="signature-image">
                            @endif
                        </div>
                        <div class="signature-line"></div>
                        <p class="small-text">{{ $preparedUser->name ?? 'N/A' }}</p>
                    </td>
                    <td style="border-right: 1px solid #000;">
                        <br>
                        <div class="signature-box">
                            @if ($checkedUser && $checkedUser->signature) 
                                <img src="{{ asset('storage/' . $checkedUser->signature) }}" alt="Signature Checked" class="signature-image">
                            @endif
                        </div>
                        <div class="signature-line"></div>
                        <p class="small-text">{{ $checkedUser->name ?? 'N/A' }}</p>
                    </td>
                    <td>
                        <div class="signature-box">
                            @if ($approvedUser && $approvedUser->signature) 
                                <img src="{{ asset('storage/' . $approvedUser->signature) }}" alt="Signature Approved" class="signature-image">
                            @endif
                        </div>
                        <div class="signature-line"></div>
                        <p class="small-text">{{ $approvedUser->name ?? 'N/A' }}</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="text-center no-print mt-5">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;">Cetak Dokumen</button>
        </div>
    </div>

</body>
</html>