<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = [
        'kode_pengeluaran',
        'tanggal_pengeluaran',
        'daftar_akun_id', // akun beban (header 5)
        'kas_bank_id',    // dibayar dari (header 1)
        'deskripsi',
        'jumlah',
        'bukti_transaksi',
    ];

    protected $casts = [
        'tanggal_pengeluaran' => 'date',
        'jumlah'              => 'decimal:2',
    ];

    /**
     * Relasi ke akun beban
     */
    public function akunBeban(): BelongsTo
    {
        return $this->belongsTo(DaftarAkun::class, 'daftar_akun_id');
    }

    /**
     * Relasi ke kas / bank (dibayar dari)
     */
    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(DaftarAkun::class, 'kas_bank_id');
    }
}