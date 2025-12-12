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
        Schema::create('purchase_request_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade'); // Relasi ke Header
            $table->string('item_name'); // Nama Barang/Jasa
            $table->string('unit')->nullable(); // Satuan (Unit, Pcs, Box, etc.)
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2)->default(0); // Harga satuan estimasi
            $table->decimal('subtotal', 15, 2)->default(0); // quantity * unit_price

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_details');
    }
};
