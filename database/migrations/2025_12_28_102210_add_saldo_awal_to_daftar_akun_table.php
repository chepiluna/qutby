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
        Schema::table('daftar_akun', function (Blueprint $table) {
            $table->decimal('saldo_awal_nominal', 15, 2)
                ->nullable()
                ->after('saldo_normal');

            $table->enum('saldo_awal_posisi', ['debit', 'kredit'])
                ->nullable()
                ->after('saldo_awal_nominal');

            $table->string('periode_awal', 7)
                ->nullable()
                ->comment('Format YYYY-MM, hanya untuk periode pertama')
                ->after('saldo_awal_posisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daftar_akun', function (Blueprint $table) {
            $table->dropColumn([
                'saldo_awal_nominal',
                'saldo_awal_posisi',
                'periode_awal',
            ]);
        });
    }
};
