<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStokAverage extends Model
{
    protected $table = 'kartu_stok_average';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'qty' => 'integer',
        'harga_beli' => 'decimal:2',
        'hpp_per_unit' => 'decimal:2',
        'hpp_total' => 'decimal:2',
        'sisa_unit' => 'integer',
        'harga_rata_rata' => 'decimal:2',
        'nilai_persediaan' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function isPembelian(): bool
    {
        return $this->jenis === 'beli';
    }
}
