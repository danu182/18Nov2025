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
        Schema::table('pr_item_references', function (Blueprint $table) {
            // Opsi 1: Ubah ke tipe TEXT (paling aman untuk link yang sangat panjang)
            $table->text('url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pr_item_references', function (Blueprint $table) {
            $table->string('url', 255)->change();
        });
    }
};
