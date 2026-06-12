<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_filter([
            'hpp_satuan',
            'harga_barang',
            'stok',
            'foto',
        ], fn (string $column): bool => Schema::hasColumn('barang', $column));

        if ($columns !== []) {
            Schema::table('barang', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->integer('hpp_satuan')->default(0);
            $table->integer('harga_barang')->default(0);
            $table->integer('stok')->default(0);
            $table->string('foto')->nullable();
        });
    }
};
