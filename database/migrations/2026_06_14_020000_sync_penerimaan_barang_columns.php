<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penerimaan_barangs')) {
            Schema::table('penerimaan_barangs', function (Blueprint $table): void {
                if (! Schema::hasColumn('penerimaan_barangs', 'jurnal_umum_id')) {
                    $table->unsignedBigInteger('jurnal_umum_id')->nullable()->after('dikonfirmasi_at');
                }
            });
        }

        if (Schema::hasTable('penerimaan_barang_details')) {
            Schema::table('penerimaan_barang_details', function (Blueprint $table): void {
                if (! Schema::hasColumn('penerimaan_barang_details', 'grn_id')) {
                    $table->foreignId('grn_id')->nullable()->after('id')->constrained('penerimaan_barangs')->cascadeOnDelete();
                }

                if (! Schema::hasColumn('penerimaan_barang_details', 'kondisi')) {
                    $table->string('kondisi')->default('baik')->after('qty_rusak');
                }

                if (! Schema::hasColumn('penerimaan_barang_details', 'foto')) {
                    $table->string('foto')->nullable()->after('kondisi');
                }

                if (! Schema::hasColumn('penerimaan_barang_details', 'catatan_item')) {
                    $table->text('catatan_item')->nullable()->after('foto');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
