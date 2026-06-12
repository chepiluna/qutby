<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Pembelian extends Model
{
    protected $table = 'pembelians';

    protected $fillable = [
        'tanggal',
        'nomor',
        'vendor_id',
        'vendor_manual',
        'total',
        'diskon',
        'ppn',
        'total_akhir',
        'status',
        'estimasi_datang',
        'status_pengiriman',
        'syarat_pembayaran',
        'referensi_pr',
        'catatan_vendor',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'estimasi_datang'   => 'date',
        'ppn'               => 'boolean',
        'total'             => 'float',
        'diskon'            => 'float',
        'total_akhir'       => 'float',
    ];

    /**
     * Generate nomor pembelian otomatis
     * Format: PO-YYYY-MM-0001
     */
    public static function generateNomorPembelian(): string
    {
        $tahun = Carbon::now()->format('Y');
        $bulan = Carbon::now()->format('m');
        $prefix = "PO-$tahun-$bulan-";

        $lastNomor = self::query()
            ->where('nomor', 'like', "$prefix%")
            ->orderByDesc('id')
            ->value('nomor');

        $nextNumber = 1;

        if ($lastNomor) {
            $lastSeq = (int) substr($lastNomor, -4);
            $nextNumber = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke Vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Detail item pembelian
     */
    public function details(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id');
    }

    /**
     * Relasi ke Penerimaan Barang (bisa lebih dari satu jika partial)
     */
    public function penerimaanBarangs(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class, 'pembelian_id');
    }

    /**
     * Relasi ke Penerimaan Barang (1 Pembelian = 1 Penerimaan Barang)
     */
    public function penerimaanBarang(): HasOne
    {
        return $this->hasOne(PenerimaanBarang::class, 'pembelian_id');
    }

    /**
     * Relasi ke Pembayaran Utang (1 Pembelian = 1 Pembayaran)
     * Pastikan tabel pembayaran punya kolom pembelian_id
     */
    public function pembayaranPembelian(): HasOne
    {
        return $this->hasOne(PembayaranPembelian::class, 'pembelian_id');
    }

    public function poTermins(): HasMany
    {
        return $this->hasMany(PoTermin::class, 'pembelian_id')->orderBy('termin_ke');
    }

    public function refreshStatusPenerimaan(): void
    {
        $details = $this->details()
            ->with(['penerimaanBarangDetails.penerimaanBarang'])
            ->get();

        if ($details->isEmpty()) {
            $this->update(['status' => 'menunggu']);
            return;
        }

        $statuses = $details->map(fn (PembelianDetail $detail) => $detail->status_penerimaan);

        $status = match (true) {
            $statuses->every(fn (string $status) => $status === 'belum_diterima') => 'menunggu',
            $statuses->every(fn (string $status) => $status === 'diterima_lengkap') => 'selesai',
            default => 'partial',
        };

        $this->update(['status' => $status]);
    }


}
