<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Grn extends Model
{
    protected $table = 'grns';

    protected $fillable = [
        'nomor_grn',
        'pembelian_id',
        'vendor_id',
        'tanggal_terima',
        'nomor_surat_jalan',
        'gudang_tujuan',
        'catatan',
        'status',
        'status_penerimaan',
        'dikonfirmasi_oleh',
        'dikonfirmasi_at',
    ];

    protected $casts = [
        'tanggal_terima'  => 'date',
        'dikonfirmasi_at' => 'datetime',
    ];

    /* ==========================
     | BOOT
     ========================== */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->nomor_grn)) {
                $model->nomor_grn = self::generateNomor();
            }

            while (self::where('nomor_grn', $model->nomor_grn)->exists()) {
                $lastNumber = (int) substr($model->nomor_grn, -4);
                $model->nomor_grn = substr($model->nomor_grn, 0, -4) . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public static function generateNomor(?int $pembelianId = null): string
    {
        $tahun = now()->year;
        $prefix = "GRN-{$tahun}-";

        if ($pembelianId) {
            $existing = self::query()
                ->where('pembelian_id', $pembelianId)
                ->orderBy('id')
                ->pluck('nomor_grn');

            if ($existing->isNotEmpty()) {
                $base = preg_replace('/-T\d+$/', '', (string) $existing->first());
                $base = preg_replace('/^PNR-/', 'GRN-', $base);

                return $base . '-T' . ($existing->count() + 1);
            }
        }

        $lastNumber = DB::table('grns')
            ->where('nomor_grn', 'like', "{$prefix}%")
            ->pluck('nomor_grn')
            ->map(function (string $nomor) use ($prefix): int {
                $withoutPrefix = str_replace($prefix, '', preg_replace('/-T\d+$/', '', $nomor));

                return preg_match('/^\d{4}$/', $withoutPrefix) ? (int) $withoutPrefix : 0;
            })
            ->max() ?? 0;

        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /* ==========================
     | RELATIONS
     ========================== */
    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GrnDetail::class, 'grn_id');
    }

    public function returPembelians(): HasMany
    {
        return $this->hasMany(ReturPembelian::class, 'grn_id');
    }

    /* ==========================
     | HELPERS
     ========================== */
    public function hasItemRusak(): bool
    {
        return $this->details()
            ->whereIn('kondisi', ['rusak_sebagian', 'rusak_semua'])
            ->exists();
    }

    public function hasSelisihQty(): bool
    {
        $this->loadMissing(['details.pembelianDetail.grnDetails.grn']);

        return $this->details->contains(function (GrnDetail $detail) {
            $poDetail = $detail->pembelianDetail;

            if (! $poDetail) {
                return false;
            }

            $qtySebelumnya = $poDetail->grnDetails
                ->filter(fn (GrnDetail $grnDetail) => $grnDetail->grn?->status === 'dikonfirmasi')
                ->sum('qty_diterima');

            return ($qtySebelumnya + (int) $detail->qty_diterima) !== (int) $poDetail->qty;
        });
    }

    public function hasOverQuantity(): bool
    {
        $this->loadMissing(['details.pembelianDetail.grnDetails.grn']);

        return $this->details->contains(function (GrnDetail $detail) {
            $poDetail = $detail->pembelianDetail;

            if (! $poDetail) {
                return false;
            }

            $qtySebelumnya = $poDetail->grnDetails
                ->filter(fn (GrnDetail $grnDetail) => $grnDetail->grn?->status === 'dikonfirmasi')
                ->sum('qty_diterima');

            return ($qtySebelumnya + (int) $detail->qty_diterima) > (int) $poDetail->qty;
        });
    }
}
