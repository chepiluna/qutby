<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuUtang extends Model
{
    protected $table = 'kartu_utangs';

    protected $fillable = [
        'vendor_id',
        'tanggal',
        'no_bukti',
        'keterangan',
        'debet',
        'kredit',
        'saldo',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
