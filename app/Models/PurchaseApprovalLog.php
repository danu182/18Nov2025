<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseApprovalLog extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'user_id',
        'action',
        'level',
        'comment',

    ];


    /**
     * Get the user that owns the PurchaseApprovalLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
