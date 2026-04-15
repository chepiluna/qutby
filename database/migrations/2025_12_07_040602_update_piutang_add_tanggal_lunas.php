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
        Schema::table('piutang', function (Blueprint $table) {
            // tanggal faktur sebagai dasar termin
            $table->date('tanggal_faktur')->nullable();

            // tanggal pelunasan (diisi saat dibayar via tabel pembayaran)
            $table->date('tanggal_lunas')->nullable();

            // relasi ke penjualan (nanti bisa dipakai kalau tabel penjualan sudah ada)
            $table->unsignedBigInteger('penjualan_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piutang', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_faktur',
                'tanggal_lunas',
                'penjualan_id',
            ]);
        });
    }
};
