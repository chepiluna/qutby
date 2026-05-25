<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrnDetail extends Model
{
    protected $table = 'grn_details';

    protected $fillable = [
        'grn_id',
        'pembelian_detail_id',
        'barang_id',
        'qty_po',
        'qty_diterima',
        'qty_rusak',
        'kondisi',
        'foto',
        'catatan_item',
    ];

    protected $casts = [
        'qty_po'       => 'integer',
        'qty_diterima' => 'integer',
        'qty_rusak'    => 'integer',
    ];

    /* ==========================
     | RELATIONS
     ========================== */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function pembelianDetail(): BelongsTo
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /* ==========================
     | HELPERS
     ========================== */
    public function getSelisihQty(): int
    {
        return $this->qty_diterima - $this->qty_po;
    }

    public function isRusak(): bool
    {
        return in_array($this->kondisi, ['rusak_sebagian', 'rusak_semua'], true);
    }
}
