<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pembayaran_vendors');
    }

    public function down(): void
    {
        Schema::create('pembayaran_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('penjualan_id')->nullable()->index('pembayaran_penjualan_id_foreign');
            $table->foreignId('piutang_id')->nullable()->index('pembayaran_piutang_id_foreign');
            $table->date('tanggal_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2);
            $table->decimal('diskon_termin', 15, 2)->default(0);
            $table->string('metode_bayar')->nullable();
            $table->foreignId('bank_akun_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->date('tanggal_diskon')->nullable();
            $table->decimal('total_setelah_diskon', 15, 2)->nullable();
            $table->foreignId('jurnal_umum_id')->nullable()->index('pembayaran_jurnal_umum_id_foreign');
        });
    }
};
