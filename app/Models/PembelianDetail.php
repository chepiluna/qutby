<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianDetail extends Model
{
    protected $table = 'pembelian_details';

    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'qty',
        'satuan',
        'harga',
        'hpp',
        'diskon_persen',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga' => 'float',
        'hpp' => 'float',
        'subtotal' => 'float',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function penerimaanBarangDetails(): HasMany
    {
        return $this->hasMany(PenerimaanBarangDetail::class, 'pembelian_detail_id');
    }

    public function getQtyDiterimaAttribute(): int
    {
        if ($this->relationLoaded('penerimaanBarangDetails')) {
            return (int) $this->penerimaanBarangDetails
                ->filter(fn (PenerimaanBarangDetail $detail) => $detail->penerimaanBarang?->status === 'dikonfirmasi')
                ->sum('qty_diterima');
        }

        return (int) $this->penerimaanBarangDetails()
            ->whereHas('penerimaanBarang', fn ($query) => $query->where('status', 'dikonfirmasi'))
            ->sum('qty_diterima');
    }

    public function getQtyOutstandingAttribute(): int
    {
        return max(0, (int) $this->qty - $this->qty_diterima);
    }

    public function getStatusPenerimaanAttribute(): string
    {
        $qtyPesan = (int) $this->qty;
        $qtyDiterima = $this->qty_diterima;

        if ($qtyDiterima === 0) {
            return 'belum_diterima';
        }

        if ($qtyDiterima < $qtyPesan) {
            return 'diterima_sebagian';
        }

        if ($qtyDiterima === $qtyPesan) {
            return 'diterima_lengkap';
        }

        return 'over_quantity';
    }

    public function getStatusPenerimaanLabelAttribute(): string
    {
        return match ($this->status_penerimaan) {
            'belum_diterima' => 'Belum Diterima',
            'diterima_sebagian' => 'Diterima Sebagian',
            'diterima_lengkap' => 'Diterima Lengkap',
            'over_quantity' => 'Over Quantity',
            default => ucfirst((string) $this->status_penerimaan),
        };
    }

    protected static function booted()
    {
        static::saved(function ($detail) {
            if ($detail->hpp > 0) {
                $detail->barang()->update(['hpp_satuan' => $detail->hpp]);
            }
        });
    }
}
