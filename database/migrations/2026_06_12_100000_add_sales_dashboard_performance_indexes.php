<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penjualan')) {
            Schema::table('penjualan', function (Blueprint $table): void {
                $table->index(['tanggal_faktur', 'cara_bayar'], 'penjualan_dashboard_tanggal_cara_idx');
            });
        }

        if (Schema::hasTable('penjualan_detail')) {
            Schema::table('penjualan_detail', function (Blueprint $table): void {
                $table->index(['penjualan_id', 'barang_id'], 'penjualan_detail_dashboard_join_idx');
            });
        }

        if (Schema::hasTable('piutang')) {
            Schema::table('piutang', function (Blueprint $table): void {
                $table->index(['status', 'tanggal_faktur'], 'piutang_dashboard_status_tanggal_idx');
            });
        }

        if (Schema::hasTable('pembelians')) {
            Schema::table('pembelians', function (Blueprint $table): void {
                $table->index(['tanggal', 'syarat_pembayaran'], 'pembelians_dashboard_tanggal_syarat_idx');
            });
        }

        if (Schema::hasTable('pembelian_details')) {
            Schema::table('pembelian_details', function (Blueprint $table): void {
                $table->index(['pembelian_id', 'barang_id'], 'pembelian_details_dashboard_join_idx');
            });
        }

        if (Schema::hasTable('penerimaan_barangs')) {
            Schema::table('penerimaan_barangs', function (Blueprint $table): void {
                $table->index(['status', 'tanggal_terima'], 'penerimaan_barangs_dashboard_status_tanggal_idx');
            });
        }

        if (Schema::hasTable('penerimaan_barang_details')) {
            Schema::table('penerimaan_barang_details', function (Blueprint $table): void {
                $table->index('grn_id', 'penerimaan_barang_details_dashboard_grn_idx');
            });
        }

        if (Schema::hasTable('kartu_stok_average')) {
            Schema::table('kartu_stok_average', function (Blueprint $table): void {
                $table->index(['barang_id', 'id'], 'kartu_stok_average_dashboard_barang_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kartu_stok_average')) {
            Schema::table('kartu_stok_average', function (Blueprint $table): void {
                $table->dropIndex('kartu_stok_average_dashboard_barang_idx');
            });
        }

        if (Schema::hasTable('penerimaan_barang_details')) {
            Schema::table('penerimaan_barang_details', function (Blueprint $table): void {
                $table->dropIndex('penerimaan_barang_details_dashboard_grn_idx');
            });
        }

        if (Schema::hasTable('penerimaan_barangs')) {
            Schema::table('penerimaan_barangs', function (Blueprint $table): void {
                $table->dropIndex('penerimaan_barangs_dashboard_status_tanggal_idx');
            });
        }

        if (Schema::hasTable('pembelian_details')) {
            Schema::table('pembelian_details', function (Blueprint $table): void {
                $table->dropIndex('pembelian_details_dashboard_join_idx');
            });
        }

        if (Schema::hasTable('pembelians')) {
            Schema::table('pembelians', function (Blueprint $table): void {
                $table->dropIndex('pembelians_dashboard_tanggal_syarat_idx');
            });
        }

        if (Schema::hasTable('piutang')) {
            Schema::table('piutang', function (Blueprint $table): void {
                $table->dropIndex('piutang_dashboard_status_tanggal_idx');
            });
        }

        if (Schema::hasTable('penjualan_detail')) {
            Schema::table('penjualan_detail', function (Blueprint $table): void {
                $table->dropIndex('penjualan_detail_dashboard_join_idx');
            });
        }

        if (Schema::hasTable('penjualan')) {
            Schema::table('penjualan', function (Blueprint $table): void {
                $table->dropIndex('penjualan_dashboard_tanggal_cara_idx');
            });
        }
    }
};
