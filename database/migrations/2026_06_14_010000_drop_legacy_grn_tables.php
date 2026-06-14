<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('grn_details');
        Schema::dropIfExists('grns');
    }

    public function down(): void
    {
        //
    }
};
