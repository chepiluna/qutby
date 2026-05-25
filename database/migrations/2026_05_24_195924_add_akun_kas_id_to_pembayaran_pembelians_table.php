<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_pembelians', function (Blueprint $table) {

            $table->foreignId('akun_kas_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('daftar_akun')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_utangs', function (Blueprint $table) {

            $table->dropForeign(['akun_kas_id']);

            $table->dropColumn('akun_kas_id');

        });
    }
};