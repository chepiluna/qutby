<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faktur_pembelians')) {
            return;
        }

        if (! Schema::hasColumn('faktur_pembelians', 'nomor_pembayaran_vendor')) {
            Schema::table('faktur_pembelians', function (Blueprint $table): void {
                $table->string('nomor_pembayaran_vendor')->nullable()->after('nomor_faktur_vendor');
            });
        }

        DB::table('faktur_pembelians')
            ->whereNull('nomor_pembayaran_vendor')
            ->where('nomor_faktur_vendor', 'like', 'BYR-%')
            ->update([
                'nomor_pembayaran_vendor' => DB::raw('nomor_faktur_vendor'),
                'nomor_faktur_vendor' => null,
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('faktur_pembelians') || ! Schema::hasColumn('faktur_pembelians', 'nomor_pembayaran_vendor')) {
            return;
        }

        Schema::table('faktur_pembelians', function (Blueprint $table): void {
            $table->dropColumn('nomor_pembayaran_vendor');
        });
    }
};
