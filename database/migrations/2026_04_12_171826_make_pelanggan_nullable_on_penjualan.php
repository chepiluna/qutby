<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->foreignId('pelanggan_id')->nullable()->change();
        });
    }

public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->foreignId('pelanggan_id')->nullable(false)->change();
        });
    }
};
