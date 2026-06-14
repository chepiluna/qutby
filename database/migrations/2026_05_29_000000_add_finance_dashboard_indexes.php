<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->index('created_at', 'pembayaran_created_at_index');
            $table->index('tanggal_bayar', 'pembayaran_tanggal_bayar_index');
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->index('created_at', 'pengeluaran_created_at_index');
            $table->index('tanggal_pengeluaran', 'pengeluaran_tanggal_pengeluaran_index');
        });

        Schema::table('penjualan', function (Blueprint $table) {
            $table->index('created_at', 'penjualan_created_at_index');
        });

        if (Schema::hasTable('po_termins')) {
            Schema::table('po_termins', function (Blueprint $table) {
                $table->index(['status', 'due_date'], 'po_termins_status_due_date_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('po_termins')) {
            Schema::table('po_termins', function (Blueprint $table) {
                $table->dropIndex('po_termins_status_due_date_index');
            });
        }

        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropIndex('penjualan_created_at_index');
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropIndex('pengeluaran_tanggal_pengeluaran_index');
            $table->dropIndex('pengeluaran_created_at_index');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex('pembayaran_tanggal_bayar_index');
            $table->dropIndex('pembayaran_created_at_index');
        });
    }
};
