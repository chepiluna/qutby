<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('po_termins');
    }

    public function down(): void
    {
        if (Schema::hasTable('po_termins')) {
            return;
        }

        Schema::create('po_termins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
            $table->integer('termin_ke')->default(1);
            $table->date('due_date')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('status')->default('belum_lunas');
            $table->date('tanggal_bayar')->nullable();
            $table->timestamps();
        });
    }
};
