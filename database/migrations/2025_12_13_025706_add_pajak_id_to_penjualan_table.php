<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            // Kolom pajak_id sudah ada, di sini hanya tambahkan foreign key-nya
            $table->foreign('pajak_id')
                ->references('id')
                ->on('pajak');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropForeign(['pajak_id']);
            // Kalau kolom pajak_id memang dibuat di migration lain, baris ini boleh dihapus
            // supaya tidak error waktu rollback.
            // $table->dropColumn('pajak_id');
        });
    }
};
