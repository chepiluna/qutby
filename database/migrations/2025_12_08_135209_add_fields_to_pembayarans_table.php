<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Hapus baris ini kalau customer_id sudah ada di tabel
            // $table->unsignedBigInteger('customer_id')->nullable();

            // Kalau kolom-kolom di bawah juga sudah ada, hapus yang dobel
            if (! Schema::hasColumn('pembayaran', 'tanggal_bayar')) {
                $table->date('tanggal_bayar')->nullable();
            }

            if (! Schema::hasColumn('pembayaran', 'jumlah_bayar')) {
                $table->decimal('jumlah_bayar', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('pembayaran', 'tanggal_diskon')) {
                $table->date('tanggal_diskon')->nullable();
            }

            if (! Schema::hasColumn('pembayaran', 'total_setelah_diskon')) {
                $table->decimal('total_setelah_diskon', 15, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Jangan drop customer_id kalau memang sudah dari awal ada
            $table->dropColumn([
                'tanggal_bayar',
                'jumlah_bayar',
                'tanggal_diskon',
                'total_setelah_diskon',
            ]);
        });
    }
};
