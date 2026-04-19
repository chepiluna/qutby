<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UP → hapus kolom saldo awal
     */
    public function up(): void
    {
        Schema::table('daftar_akun', function (Blueprint $table) {
            if (Schema::hasColumn('daftar_akun', 'saldo_awal_nominal')) {
                $table->dropColumn('saldo_awal_nominal');
            }
        });
    }

    /**
     * DOWN → balikin kolom (rollback)
     */
    public function down(): void
    {
        Schema::table('daftar_akun', function (Blueprint $table) {
            $table->decimal('saldo_awal_nominal', 15, 2)
                  ->default(0)
                  ->after('saldo_normal');
        });
    }
};