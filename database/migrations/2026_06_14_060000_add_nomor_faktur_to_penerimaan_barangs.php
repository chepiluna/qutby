<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penerimaan_barangs') || Schema::hasColumn('penerimaan_barangs', 'nomor_faktur')) {
            return;
        }

        Schema::table('penerimaan_barangs', function (Blueprint $table): void {
            $table->string('nomor_faktur')->nullable()->after('tanggal_terima');
        });
    }

    public function down(): void
    {
        //
    }
};
