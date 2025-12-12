{{-- components/po_status_badge.blade.php --}}
@php
    $class = match($status) {
        'Draft' => 'bg-secondary',
        'Pending Approval' => 'bg-warning text-dark',
        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger',
        'Completed' => 'bg-primary',
        default => 'bg-info',
    };
@endphp

<span class="badge {{ $class }}">{{ $status }}</span>