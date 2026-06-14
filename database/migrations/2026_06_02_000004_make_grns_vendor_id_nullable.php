<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barangs', function (Blueprint $table): void {
            $table->unsignedBigInteger('vendor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barangs', function (Blueprint $table): void {
            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
        });
    }
};
