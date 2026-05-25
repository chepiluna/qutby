<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',

        'is_saldo_awal' => 'boolean',

        // Skema lama
        'masuk' => 'integer',
        'harga_masuk' => 'integer',
        'keluar' => 'integer',
        'harga_keluar' => 'integer',

        // Skema baru (gunakan integer untuk rupiah & unit)
        'pembelian_unit' => 'integer',
        'pembelian_harga_unit' => 'integer',
        'pembelian_total' => 'integer',

        'hpp_unit' => 'integer',
        'hpp_harga_unit' => 'integer',
        'hpp_total' => 'integer',

        'saldo_unit' => 'integer',
        'saldo_harga_unit' => 'integer',
        'saldo_total' => 'integer',
    ];

    /**
     * =========================
     * RELATIONSHIPS
     * =========================
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */
    public function isSaldoAwal(): bool
    {
        // Prioritas: flag boolean jika tersedia
        if (!is_null($this->is_saldo_awal)) {
            return (bool) $this->is_saldo_awal;
        }

        // Fallback: berdasarkan keterangan
        return strtoupper((string) $this->keterangan) === 'SALDO AWAL';
    }
}
