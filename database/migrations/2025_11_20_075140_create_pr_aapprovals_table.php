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
        Schema::create('pr_aapprovals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->integer('level'); // Level persetujuan (1, 2, 3, dst.)
            $table->foreignId('approver_id')->constrained('users'); // User yang bertindak menyetujui
            $table->string('action')->comment('Pending, Approved, Rejected'); // Aksi yang diambil
            $table->text('notes')->nullable(); // Catatan atau alasan
            $table->dateTime('action_at')->nullable(); // Waktu aksi

            $table->timestamps();

            $table->unique(['purchase_request_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_aapprovals');
    }
};
