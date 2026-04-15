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
        Schema::create('daftar_akun', function (Blueprint $table) {
            $table->id();

            // 1 = Aset, 2 = Utang, 3 = Modal, 4 = Pendapatan, 5 = Beban, dll
            $table->unsignedTinyInteger('header_akun');

            $table->string('kode_akun')->unique();
            $table->string('nama_akun');

            // akun induk (optional, buat struktur bertingkat)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('daftar_akun');   // self‑reference [web:400][web:411]

            // saldo normal akun
            $table->enum('saldo_normal', ['debit', 'kredit']); // [web:300][web:398]

            $table->timestamps(); // created_at & updated_at [web:297]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_akun'); // [web:297]
    }
};
