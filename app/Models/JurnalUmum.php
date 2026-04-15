<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JurnalUmum extends Model
{
    use HasFactory;

    protected $table = 'jurnal_umum';

    protected $fillable = [
        'tanggal',
        'kode_jurnal',
        'deskripsi',
        'transaksi_type', // class Penjualan / Pembayaran / dst.
        'transaksi_id',
    ];

    // pakai lowerCamelCase: 'details'
    public function details(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class, 'jurnal_umum_id');
    }

    public function transaksi(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'transaksi_type', 'transaksi_id');
    }
}
