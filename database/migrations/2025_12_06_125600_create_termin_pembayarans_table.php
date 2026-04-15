<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('termin_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();          // contoh: 2_10_n_30
            $table->string('nama');                    // contoh: "2/10, n/30"
            $table->unsignedTinyInteger('diskon_persen')->default(0);
            $table->unsignedTinyInteger('hari_diskon')->nullable();
            $table->unsignedTinyInteger('hari_jatuh_tempo')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('termin_pembayaran');
    }
};
