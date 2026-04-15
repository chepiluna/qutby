<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KategoriPengeluaran extends Model
{
    protected $table = 'kategori_pengeluaran';

    protected $fillable = [
        'nama',
        'deskripsi',
        'daftar_akun_id',
    ];
    
    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'kategori_pengeluaran_id');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(
            DaftarAkun::class,
            'daftar_akun_id', // FK di tabel kategori_pengeluaran
            'id'              // PK di daftar_akun
        );
    }
}
