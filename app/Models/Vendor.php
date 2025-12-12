<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'contact_person', 
        'npwp', 
        'address',
        'notes',
        'is_active',
    ];


    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id', 'id');
    }


}
