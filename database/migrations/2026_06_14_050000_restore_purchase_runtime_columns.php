<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembelians')) {
            Schema::table('pembelians', function (Blueprint $table): void {
                if (! Schema::hasColumn('pembelians', 'status')) {
                    $table->string('status')->default('menunggu')->after('total_akhir');
                }

                if (! Schema::hasColumn('pembelians', 'estimasi_datang')) {
                    $table->date('estimasi_datang')->nullable()->after('status');
                }

                if (! Schema::hasColumn('pembelians', 'status_pengiriman')) {
                    $table->string('status_pengiriman')->nullable()->after('estimasi_datang');
                }

                if (! Schema::hasColumn('pembelians', 'referensi_pr')) {
                    $table->string('referensi_pr')->nullable()->after('syarat_pembayaran');
                }

                if (! Schema::hasColumn('pembelians', 'catatan_vendor')) {
                    $table->text('catatan_vendor')->nullable()->after('referensi_pr');
                }
            });
        }

        if (Schema::hasTable('penerimaan_barangs')) {
            Schema::table('penerimaan_barangs', function (Blueprint $table): void {
                if (! Schema::hasColumn('penerimaan_barangs', 'nomor_surat_jalan')) {
                    $table->string('nomor_surat_jalan')->nullable()->after('tanggal_terima');
                }

                if (! Schema::hasColumn('penerimaan_barangs', 'gudang_tujuan')) {
                    $table->string('gudang_tujuan')->nullable()->after('nomor_surat_jalan');
                }

                if (! Schema::hasColumn('penerimaan_barangs', 'catatan')) {
                    $table->text('catatan')->nullable()->after('gudang_tujuan');
                }

                if (! Schema::hasColumn('penerimaan_barangs', 'dikonfirmasi_oleh')) {
                    $table->unsignedBigInteger('dikonfirmasi_oleh')->nullable()->after('status_penerimaan');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
