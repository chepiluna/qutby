<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penerimaan_barangs') || ! Schema::hasColumn('penerimaan_barangs', 'nomor_grn')) {
            return;
        }

        $indexExists = collect(DB::select("SHOW INDEX FROM penerimaan_barangs WHERE Key_name = 'penerimaan_barangs_nomor_grn_unique'"))->isNotEmpty();

        Schema::table('penerimaan_barangs', function (Blueprint $table) use ($indexExists): void {
            if ($indexExists) {
                $table->dropUnique('penerimaan_barangs_nomor_grn_unique');
            }

            $table->dropColumn('nomor_grn');
        });
    }

    public function down(): void
    {
        //
    }
};
