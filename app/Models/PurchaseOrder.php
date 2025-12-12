<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PurchaseOrder extends Model
{
    
    
    protected $fillable = [
        'pr_id',
        'po_number',
        'vendor_id',
        'po_date',
        'required_delivery_date',
        'terms_of_payment',
        'shipping_address',
        'currency', 
        'subtotal', 
        'tax_amount',
        'total_amount',
        'status', 
        'created_by',
    ];



    // public function purchaseOrder()
    // {
    //     // Asumsi Anda memiliki model PurchaseOrder dan PO memiliki foreign key 'pr_id'
    //     return $this->hasOne(PurchaseOrder::class, 'pr_id');
    // }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequests::class, 'pr_id');
    }


     /**
     * Relasi ke Vendor yang dituju
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id'); // Asumsi ada model Vendor
    }


    /**
     * Relasi ke Detail Item PO
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_id');
    }

    /**
     * Relasi ke User yang membuat PO
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by'); // Asumsi model User
    }

    
    // public function purchaseApprovalLog(): BelongsTo
    // {
    //     return $this->belongsTo(PurchaseApprovalLog::class, 'id', 'purchase_order_id');
    // }

    public function purchaseApprovalLogs() 
    {
        // Asumsi: purchase_order_id adalah foreign key di tabel purchase_approval_logs
        return $this->hasMany(PurchaseApprovalLog::class, 'purchase_order_id'); 
    }

    


}
