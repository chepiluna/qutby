<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel pelanggan (tabel: 'pelanggan')
            $table->foreignId('pelanggan_id')
                ->constrained('pelanggan')
                ->cascadeOnDelete();

            $table->string('no_faktur')->nullable();

            // Termin pembayaran, contoh 2/10, n/30
            $table->unsignedTinyInteger('diskon_persen')->default(0);      // 2
            $table->unsignedTinyInteger('hari_diskon')->nullable();        // 10
            $table->unsignedTinyInteger('hari_jatuh_tempo')->default(30);  // 30

            // Opsional: simpan tanggal jatuh tempo final
            $table->date('tgl_jatuh_tempo')->nullable();

            // Nominal piutang
            $table->decimal('total_piutang', 15, 2);
            $table->decimal('sisa_piutang', 15, 2);

            // Status: belum_lunas / lunas
            $table->string('status')->default('belum_lunas');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};
