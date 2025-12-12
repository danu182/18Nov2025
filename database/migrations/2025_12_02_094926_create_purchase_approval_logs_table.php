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
        Schema::create('purchase_approval_logs', function (Blueprint $table) {
            $table->id();

            // Link ke Purchase Order utama
            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->onDelete('cascade'); 
            
            // Siapa yang melakukan aksi
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // Aksi yang dilakukan
            $table->enum('action', [
                'SUBMIT', 
                'APPROVED_L1', 
                'APPROVED_L2', 
                'REJECTED', 
                'REVISED'
            ]);
            
            // Level persetujuan saat aksi terjadi
            $table->integer('level')->nullable(); 

            $table->text('comment')->nullable(); // Komentar dari Approver/Buyer


            $table->timestamps();

            // $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_approval_logs');
    }
};
