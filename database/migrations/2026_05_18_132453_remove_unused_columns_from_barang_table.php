<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn([
                'hpp_satuan',
                'harga_barang',
                'stok',
                'foto',
            ]);
        });
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