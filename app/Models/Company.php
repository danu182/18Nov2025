<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'pic',
        'address',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all of the comments for the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function purchaseOrder()
    // {
    //     return $this->hasMany(PurchaseOrder::class, 'vendor_id', 'id');
    // }

}
