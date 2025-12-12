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
        Schema::create('pr_item_references', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke item detail PR (PurchaseRequestDetail)
            // $table->foreignId('purchase_request_detail_id')
            //     ->constrained('purchase_request_details')
            //     ->onDelete('cascade');

            $table->foreignId('purchase_request_detail_id') // <--- PASTI DI SINI
                ->constrained('purchase_request_details')
                ->onDelete('cascade');

            $table->string('url'); // Kolom untuk menyimpan link/URL
            $table->string('description')->nullable(); // Deskripsi opsional

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_item_references');
    }
};
