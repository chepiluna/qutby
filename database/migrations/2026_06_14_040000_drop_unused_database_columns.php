<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pembayaran_pembelians');

        $this->dropColumnsIfExist('faktur_pembelians', [
            'nomor_faktur',
            'no_faktur',
            'jatuh_tempo',
            'detail',
            'subtotal',
            'diskon',
            'dpp',
            'ppn',
            'total_faktur',
            'status',
        ]);

        $this->dropColumnsIfExist('kartu_stok', [
            'source_line_id',
        ]);

        $this->dropColumnsIfExist('kartu_stok_average', [
            'transaksi_tipe',
        ]);
    }

    public function down(): void
    {
        //
    }

    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }
};
