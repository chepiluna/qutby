<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FakturPembelian extends Model
{
    protected $table = 'faktur_pembelians';

    protected $fillable = [
        'tanggal_faktur',
        'nomor_faktur_vendor',
        'pembelian_id',
        'vendor_id',
        'total_bruto',
        'diskon_persen',
        'total_netto',
        'bukti_pembayaran',
    ];

    /**
     * NOTE:
     * Anda memakai tabel detail terpisah via relasi `details()`.
     * Jadi cast JSON 'detail' tidak dibutuhkan, kecuali Anda memang punya kolom `detail` di tabel faktur_pembelians.
     * Kalau tidak ada kolomnya, lebih aman dihapus supaya tidak membingungkan.
     */
    // protected $casts = [
    //     'detail' => 'array',
    // ];

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
        return $this->hasMany(FakturPembelianDetail::class);
    }

    /**
     * ✅ Satu relasi pembayaran saja (hapus duplikat).
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(PembayaranPembelian::class, 'faktur_pembelian_id');
    }

    /**
     * ✅ Total dihitung dari detail faktur (qty * harga).
     * Pastikan `details` diload di Filament kalau mau dipakai di UI.
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->details->sum(fn ($d) => ((float) $d->qty) * ((float) $d->harga));
    }

    protected static function booted()
    {
        // Ketika FakturPembayaran / Pembayaran berhasil dibuat
        static::created(function (self $faktur) {
            if ($faktur->pembelian) {
                $faktur->pembelian->update(['status' => 'lunas']);
            }
        });

        // (Opsional) Jika pembayaran dihapus, status dikembalikan
        static::deleted(function (self $faktur) {
            if ($faktur->pembelian) {
                // Dianggap belum dibayar kembali
                $faktur->pembelian->update(['status' => null]); 
            }
        });
    }
}