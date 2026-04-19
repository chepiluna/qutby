<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {

            // ❌ hapus kategori (kalau ada)
            if (Schema::hasColumn('pengeluaran', 'kategori_pengeluaran_id')) {
                try {
                    $table->dropForeign(['kategori_pengeluaran_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('kategori_pengeluaran_id');
            }

            // ❌ hapus status
            if (Schema::hasColumn('pengeluaran', 'status')) {
                $table->dropColumn('status');
            }

            // ✅ TAMBAH KOLOM BARU (TANPA FK DULU)
            if (!Schema::hasColumn('pengeluaran', 'daftar_akun_id')) {
                $table->unsignedBigInteger('daftar_akun_id')
                    ->nullable()
                    ->after('tanggal_pengeluaran');
            }

            if (!Schema::hasColumn('pengeluaran', 'kas_bank_id')) {
                $table->unsignedBigInteger('kas_bank_id')
                    ->nullable()
                    ->after('daftar_akun_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {

            if (Schema::hasColumn('pengeluaran', 'daftar_akun_id')) {
                $table->dropColumn('daftar_akun_id');
            }

            if (Schema::hasColumn('pengeluaran', 'kas_bank_id')) {
                $table->dropColumn('kas_bank_id');
            }

            // balikin (optional)
            $table->unsignedBigInteger('kategori_pengeluaran_id')->nullable();
            $table->string('status')->nullable();
        });
    }
};