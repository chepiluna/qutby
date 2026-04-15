<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminPembayaran extends Model
{
    use HasFactory;

    protected $table = 'termin_pembayaran';

    protected $fillable = [
        'kode',
        'nama',
        'diskon_persen',
        'hari_diskon',
        'hari_jatuh_tempo',
    ];

    public function piutang(): HasMany
    {
        return $this->hasMany(Piutang::class, 'termin_id');
    }
    
}
