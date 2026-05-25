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

        $lastNomor = self::query()
            ->where('nomor_faktur_vendor', 'like', "$prefix%")
            ->orderByDesc('id')
            ->value('nomor_faktur_vendor');

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
        return $this->attributes['nomor_faktur_vendor'] ?? null;
    }

    public function setNomorPembayaranVendorAttribute(mixed $value): void
    {
        $this->attributes['nomor_faktur_vendor'] = $value;
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

        if (($pembelian->syarat_pembayaran ?? 'kredit') === 'tunai') {
            return 'Lunas';
        }

        $termins = $pembelian->poTermins;

        if ($termins->isEmpty()) {
            return 'Kredit';
        }

        if ($termins->every(fn (PoTermin $termin): bool => $termin->status === 'lunas')) {
            return 'Lunas';
        }

        return 'Tahap ' . $this->tahapPembayaranKe();
    }

    public function tahapPembayaranKe(): int
    {
        if (! $this->pembelian_id) {
            return 1;
        }

        $paidCount = self::query()
            ->where('pembelian_id', $this->pembelian_id)
            ->when($this->exists, fn ($query) => $query->where('id', '<=', $this->id))
            ->count();

        if (! $this->exists) {
            $paidCount++;
        }

        $terminCount = $this->pembelian?->poTermins?->count() ?: 1;

        return min(max($paidCount, 1), $terminCount);
    }

    protected static function booted(): void
    {
        static::creating(function (self $pembayaran) {
            if (blank($pembayaran->nomor_pembayaran_vendor)) {
                $pembayaran->nomor_pembayaran_vendor = self::generateNomorPembayaranVendor();
            }
        });

        static::created(function (self $pembayaran) {
            $pembelian = $pembayaran->pembelian()->with('poTermins')->first();

            if (! $pembelian) {
                return;
            }

            if (($pembelian->syarat_pembayaran ?? 'kredit') === 'tunai') {
                $pembelian->update(['status' => 'lunas']);
                return;
            }

            $termin = $pembelian->poTermins
                ->where('status', '!=', 'lunas')
                ->sortBy('termin_ke')
                ->first();

            $termin?->update([
                'status' => 'lunas',
                'tanggal_bayar' => $pembayaran->tanggal_pembayaran,
            ]);

            $pembelian->refresh();

            if ($pembelian->poTermins()->where('status', '!=', 'lunas')->doesntExist()) {
                $pembelian->update(['status' => 'lunas']);
            }
        });

        static::deleted(function (self $pembayaran) {
            $pembayaran->pembelian?->update(['status' => null]);
        });
    }
}
