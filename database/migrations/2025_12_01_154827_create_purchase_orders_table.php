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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pr_id')->constrained('purchase_requests')->onDelete('cascade'); // Link ke PR asal
            $table->string('po_number')->unique(); // Nomor PO yang akan dicetak (PO/PT/Y-MM/XXXX)
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null'); // Vendor yang dituju
            $table->date('po_date'); // Tanggal PO dibuat
            $table->date('required_delivery_date')->nullable(); // Tanggal pengiriman yang diminta
            $table->text('terms_of_payment')->nullable(); // Syarat pembayaran (e.g., Net 30 days)
            $table->text('shipping_address')->nullable(); // Alamat pengiriman
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0); // Jumlah PPN/Pajak
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['Draft', 'Sent to Vendor', 'Received', 'Cancelled'])->default('Draft');
            $table->foreignId('created_by')->constrained('users'); // Siapa yang membuat PO
            


            $table->timestamps();
            // $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
