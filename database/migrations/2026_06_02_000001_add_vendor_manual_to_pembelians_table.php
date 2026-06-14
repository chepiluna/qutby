<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table): void {
            if (! Schema::hasColumn('pembelians', 'vendor_manual')) {
                $table->string('vendor_manual')->nullable()->after('vendor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table): void {
            if (Schema::hasColumn('pembelians', 'vendor_manual')) {
                $table->dropColumn('vendor_manual');
            }
        });
    }
};
