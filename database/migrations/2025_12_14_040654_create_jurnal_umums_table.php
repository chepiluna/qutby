<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_umum', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->string('kode_jurnal')->unique();
            $table->string('deskripsi')->nullable();

            // relasi polymorphic ke transaksi (Penjualan, Pembayaran, dll.)
            $table->string('transaksi_type')->nullable();   // contoh: App\Models\Penjualan
            $table->unsignedBigInteger('transaksi_id')->nullable();

            $table->timestamps();

            $table->index(['transaksi_type', 'transaksi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum');
    }
};
