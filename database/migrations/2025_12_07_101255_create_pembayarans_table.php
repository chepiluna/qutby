<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            // pembayaran bisa langsung ke penjualan (tunai) ATAU ke piutang (kredit)
            $table->foreignId('penjualan_id')
                ->nullable()
                ->constrained('penjualan'); // sesuaikan nama tabel penjualanmu

            $table->foreignId('piutang_id')
                ->nullable()
                ->constrained('piutang')
                ->cascadeOnDelete();

            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->decimal('diskon_termin', 15, 2)->default(0);
            $table->string('metode_bayar')->nullable();   // cash, transfer, dll
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
