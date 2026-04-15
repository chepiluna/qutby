<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DaftarAkun extends Model
{
    protected $table = 'daftar_akun';

    protected $fillable = [
        'header_akun',
        'kode_akun',
        'nama_akun',
        'parent_id',
        'saldo_normal',
        'saldo_awal_nominal',
    ];

    /**
     * Parent akun (hierarki COA)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child akun (hierarki COA)
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Relasi ke detail jurnal umum
     * jurnal_umum_details.daftar_akun_id → daftar_akun.id
     */
    public function jurnalUmumDetails(): HasMany
    {
        return $this->hasMany(JurnalUmumDetail::class, 'daftar_akun_id');
    }

    /**
     * Relasi ke kategori pengeluaran
     * kategori_pengeluaran.daftar_akun_id → daftar_akun.id
     */
    public function kategoriPengeluarans(): HasMany
    {
        return $this->hasMany(KategoriPengeluaran::class, 'daftar_akun_id');
    }
}
