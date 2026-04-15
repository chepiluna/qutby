<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\JurnalUmum;
use App\Models\DaftarAkun;


class JurnalUmumDetail extends Model
{
    use HasFactory;

    protected $table = 'jurnal_umum_details';

    protected $fillable = [
        'jurnal_umum_id',
        'daftar_akun_id',
        'posisi',   // 'debit' atau 'kredit'
        'nominal',
    ];

    public function jurnalUmum(): BelongsTo
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_umum_id');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(DaftarAkun::class, 'daftar_akun_id');
    }
}
