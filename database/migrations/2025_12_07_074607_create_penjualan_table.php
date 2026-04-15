<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique();
            $table->date('tanggal_faktur');

            $table->foreignId('pelanggan_id')->constrained('pelanggan');
            $table->foreignId('termin_id')->nullable()->constrained('termin_pembayaran');

            $table->decimal('total_bruto', 15, 2)->default(0);
            $table->decimal('diskon_rp', 15, 2)->default(0);
            $table->decimal('total_netto', 15, 2)->default(0);

            $table->enum('cara_bayar', ['tunai', 'kredit'])->default('tunai');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
