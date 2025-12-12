<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade'); // Link ke PO utama
            $table->string('item_name');
            $table->decimal('quantity', 15, 2);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 15, 2); // Harga per unit
            $table->decimal('subtotal', 15, 2); // subtotal per baris (quantity * unit_price)


            $table->timestamps();

            // $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
