<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'sales')
            ->update(['role' => 'operasional']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'operasional')
            ->update(['role' => 'sales']);
    }
};
