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
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id(); // primary key auto increment
            // Kode unik pelanggan, misalnya PLG001, PLG002, ...
            $table->string('kode_pelanggan')->unique();
            // Sesuai class diagram: nama, alamat, no_telp
            $table->string('nama_pelanggan');
            $table->text('alamat')->nullable();
            $table->string('no_telp', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
