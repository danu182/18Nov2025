<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    // protected $guarded = ['id']; // Gunakan guarded untuk melindungi semua kecuali ID

    protected $fillable = [
        'po_id',
        'item_name',
        'quantity',
        'unit', 
        'unit_price', // Harga per unit
        'subtotal',  // subtotal per baris (quantity * unit_price)

];


    /**
     * Relasi ke Purchase Order induk
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
