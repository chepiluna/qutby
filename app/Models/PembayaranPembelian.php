<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembayaranPembelian extends Model
{
    protected $table = 'faktur_pembelians';

    protected $fillable = [
        'tanggal_faktur',
        'tanggal_pembayaran',
        'nomor_faktur_vendor',
        'nomor_pembayaran_vendor',
        'pembelian_id',
        'grn_id',
        'vendor_id',
        'total_bruto',
        'diskon_persen',
        'total_netto',
        'bukti_pembayaran',
    ];

    public static function generateNomorPembayaranVendor(): string
    {
        $tahun = now()->format('Y');
        $bulan = now()->format('m');
        $prefix = "BYR-$tahun-$bulan-";

        $lastNomorPembayaran = self::query()
            ->where('nomor_pembayaran_vendor', 'like', "$prefix%")
            ->orderByDesc('id')
            ->value('nomor_pembayaran_vendor');

        $lastNomorLegacy = self::query()
            ->where('nomor_faktur_vendor', 'like', "$prefix%")
            ->orderByDesc('id')
            ->value('nomor_faktur_vendor');

        $lastNomor = $lastNomorPembayaran ?: $lastNomorLegacy;

        $nextNumber = 1;

        if ($lastNomor) {
            $lastSeq = (int) substr($lastNomor, -4);
            $nextNumber = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function penerimaanBarang(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class, 'grn_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PembayaranPembelianDetail::class, 'faktur_pembelian_id');
    }

    public function getTanggalPembayaranAttribute(): mixed
    {
        return $this->attributes['tanggal_faktur'] ?? null;
    }

    public function setTanggalPembayaranAttribute(mixed $value): void
    {
        $this->attributes['tanggal_faktur'] = $value;
    }

    public function getNomorPembayaranVendorAttribute(): mixed
    {
        return $this->attributes['nomor_pembayaran_vendor']
            ?? $this->attributes['nomor_faktur_vendor']
            ?? null;
    }

    public function setNomorPembayaranVendorAttribute(mixed $value): void
    {
        $this->attributes['nomor_pembayaran_vendor'] = $value;
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->details->sum(fn ($detail) => ((float) $detail->qty) * ((float) $detail->harga));
    }

    public function getStatusPembayaranUtangAttribute(): string
    {
        $pembelian = $this->pembelian;

        if (! $pembelian) {
            return '-';
        }

        return 'Lunas';
    }

    protected static function booted(): void
    {
        static::creating(function (self $pembayaran) {
            if (blank($pembayaran->getAttributes()['nomor_pembayaran_vendor'] ?? null)) {
                $pembayaran->nomor_pembayaran_vendor = self::generateNomorPembayaranVendor();
            }
        });

        static::created(function (self $pembayaran) {
            $pembelian = $pembayaran->pembelian;

            if (! $pembelian) {
                return;
            }

            $pembelian->update(['status' => 'lunas']);
        });

        static::deleted(function (self $pembayaran) {
            $pembayaran->pembelian?->update(['status' => null]);
        });
    }
}
