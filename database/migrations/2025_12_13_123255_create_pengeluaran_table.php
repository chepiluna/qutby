<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengeluaran')->unique();
            $table->date('tanggal_pengeluaran');
            $table->foreignId('kategori_pengeluaran_id')
                  ->constrained('kategori_pengeluaran'); // tabel kategori
            $table->string('deskripsi')->nullable();
            $table->decimal('jumlah', 15, 2);
            $table->enum('status', ['dibayar', 'belum_dibayar'])
                  ->default('belum_dibayar');
            $table->string('bukti_transaksi')->nullable(); // path file bukti
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};