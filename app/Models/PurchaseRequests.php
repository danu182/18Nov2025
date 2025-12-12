<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequests extends Model
{

    use HasFactory;

    protected $fillable = [
        'pr_number', 
        'pr_date', 
        'requested_by', 
        'purpose', 
        'total_amount',
        'status', 
        'current_approver_id', // Tambahkan ini
        'approval_date', // Tambahkan ini
    ];


    protected $casts = [
        'pr_date' => 'datetime', // atau 'date'
    ];


    // public function details()
    // {
    //     // Memaksa Laravel menggunakan 'purchase_request_id' saat menyimpan detail
    //     return $this->hasMany(PurchaseRequestDetails::class, 'purchase_request_id'); 
    // }

    /**
     * Get all of the comments fo PurchaseRequests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(PurchaseRequestDetails::class, 'purchase_request_id', 'id');
    }
    
    // Relasi Requester
    public function requester()
    {
        // Ganti 'requested_by' jika Foreign Key-nya berbeda
        return $this->belongsTo(User::class, 'requested_by'); 
    }

    // Relasi baru untuk riwayat persetujuan
    // public function approvals()
    // {
    //     return $this->hasMany(PrAapproval::class);
    // }

    public function approvals()
    {
        return $this->hasMany(PrAapproval::class, 'purchase_request_id', 'id');
    }
    
    // Relasi ke User yang harus menyetujui saat ini
    // public function currentApprover()
    // {
    //     return $this->belongsTo(User::class, 'current_approver_id');
    // }


    // Relasi Current Approver
    public function currentApprover()
    {
        // Ganti 'current_approver_id' jika Foreign Key-nya berbeda
        return $this->belongsTo(PrAapproval ::class, 'purchase_request_id'); 
    }


    public function purchaseOrder()
    {
        // PR memiliki satu PO
        return $this->hasOne(PurchaseOrder::class, 'pr_id');
    }



   /**
    * Get all of the comments for the PurchaseRequests
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   
    

}
