<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    // Nama tabel di database
    protected $table = 'pengeluaran';

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'kode_pengeluaran',
        'tanggal_pengeluaran',
        'kategori_pengeluaran_id',
        'deskripsi',
        'jumlah',
        'status',
        'bukti_transaksi',
    ];

    // Casting tipe data
    protected $casts = [
        'tanggal_pengeluaran' => 'date',
        'jumlah'              => 'decimal:2',
    ];

    // Relasi ke tabel kategori_pengeluaran
    public function kategoriPengeluaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'kategori_pengeluaran_id');
    }
    
}
