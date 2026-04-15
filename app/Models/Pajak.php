<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    // kalau nama tabel bukan "pajaks" tapi "pajak"
    protected $table = 'pajak';

    // kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'kode',    // contoh: PPN11
        'nama',    // contoh: Pajak PPN 11%
        'persen',  // contoh: 11.00
    ];
}
