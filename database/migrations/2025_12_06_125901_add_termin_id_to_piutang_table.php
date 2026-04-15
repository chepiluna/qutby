<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piutang', function (Blueprint $table) {
            $table->foreignId('termin_id')
                ->nullable()
                ->constrained('termin_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('piutang', function (Blueprint $table) {
            $table->dropConstrainedForeignId('termin_id');
        });
    }
};
