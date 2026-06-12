<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            if (! Schema::hasColumn('pembelians', 'catatan_vendor')) {
                $table->text('catatan_vendor')->nullable()->after('referensi_pr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table): void {
            foreach (['catatan_vendor', 'status_pengiriman', 'estimasi_datang', 'status'] as $column) {
                if (Schema::hasColumn('pembelians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
