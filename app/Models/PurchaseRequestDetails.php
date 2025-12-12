<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id', 'item_name', 'unit', 'quantity', 'unit_price', 'subtotal'
    ];

    public function purchaseRequest()
    {
        // Secara eksplisit mendefinisikan foreign key: 'purchase_request_id'
        return $this->belongsTo(PurchaseRequests::class, 'purchase_request_id'); 
    }

    public function references()
    {
        // return $this->hasMany(PrItemReference::class);
        return $this->hasMany(PrItemReference::class, 'purchase_request_detail_id');
    }


    // public function details()
    // {
    //     // Ganti PurchaseRequestDetail::class sesuai nama model detail Anda
    //     return $this->hasMany(PurchaseRequestDetail::class, 'purchase_request_id'); 
    // }


}
