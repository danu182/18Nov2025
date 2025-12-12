<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItemReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_detail_id',
        'url',
        'description',
    ];

    public function detail()
    {
        // return $this->belongsTo(PurchaseRequestDetails::class);
        return $this->belongsTo(PurchaseRequestDetails::class, 'purchase_request_detail_id');
    }
}
