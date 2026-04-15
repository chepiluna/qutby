<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_umum_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jurnal_umum_id')
                ->constrained('jurnal_umum')
                ->onDelete('cascade');

            $table->foreignId('daftar_akun_id')
                ->constrained('daftar_akun')
                ->onDelete('restrict');

            $table->enum('posisi', ['debit', 'kredit']);
            $table->decimal('nominal', 18, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_umum_details');
    }
};
