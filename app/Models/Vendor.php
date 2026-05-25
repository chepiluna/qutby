<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    protected $table = 'vendors';

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'kode_vendor',
        'nama_vendor',
        'alamat',
        'no_telepon',
        'email',
        'diskon_persen',
        'nama_bank',
        'nomor_rekening',
        'periode_pembayaran',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'diskon_persen' => 'decimal:2',
    ];

    /**
     * Auto-generate Kode Vendor
     * Contoh: VND-0001
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_vendor)) {
                $model->kode_vendor = self::generateKodeVendor();
            }
        });
    }

    public static function generateKodeVendor(): string
    {
        $last = DB::table('vendors')
            ->whereNotNull('kode_vendor')
            ->orderByDesc('id')
            ->value('kode_vendor');

        $lastNumber = $last
            ? (int) substr($last, -4)
            : 0;

        return 'VND-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /* ======================
     | RELATIONS (OPSIONAL)
     ====================== */

    // Jika vendor punya banyak pembelian
    public function pembelians()
    {
        return $this->hasMany(Pembelian::class, 'vendor_id');
    }

    // Jika vendor punya banyak penerimaan barang
    public function penerimaanBarangs()
    {
        return $this->hasMany(PenerimaanBarang::class, 'vendor_id');
    }
}
