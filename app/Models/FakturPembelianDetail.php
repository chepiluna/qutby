<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakturPembelianDetail extends Model
{
    protected $table = 'faktur_pembelian_details'; // sesuaikan

    protected $fillable = [
      'faktur_pembelian_id',
        'barang_id',
        'qty',
        'harga',
        'subtotal',
    ];

    public function fakturPembelian()
    {
        return $this->belongsTo(FakturPembelian::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
