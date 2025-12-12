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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            $table->string('pr_number')->unique(); // Nomor PR (misal: PR/001/2025)
            $table->date('pr_date'); // Tanggal Permintaan
            $table->foreignId('requested_by')->constrained('users'); // User yang meminta
            $table->text('purpose')->nullable(); // Tujuan Permintaan
            // $table->string('status')->default('Draft'); // Status: Draft, Submitted, Approved, Rejected
            $table->decimal('total_amount', 15, 2)->default(0); // Total Jumlah seluruh item

            $table->string('status')->default('Draft'); // Draft, Pending, Approved, Rejected
            
            // Kolom baru untuk Approval
            $table->foreignId('current_approver_id')->nullable()->constrained('users'); // User yang harus menyetujui saat ini
            $table->dateTime('approval_date')->nullable(); // Tanggal persetujuan final

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
