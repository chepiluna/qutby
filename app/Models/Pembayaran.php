<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JurnalUmum;

class Pembayaran extends Model
{
    use HasFactory;

    // nama tabel
    protected $table = 'pembayaran';

    protected $fillable = [
        'customer_id',
        'penjualan_id',
        'piutang_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'diskon_termin',
        'tanggal_diskon',
        'total_setelah_diskon',
        'metode_bayar',
        'keterangan',
        'jenis',
    ];

    protected $casts = [
        'tanggal_bayar'        => 'date',
        'tanggal_diskon'       => 'date',
        'jumlah_bayar'         => 'decimal:2',
        'diskon_termin'        => 'decimal:2',
        'total_setelah_diskon' => 'decimal:2',
    ];

    // --- Relasi ---

    public function pelanggan()
    {
    // customer_id di pembayaran -> id di pelanggan
    return $this->belongsTo(Pelanggan::class, 'customer_id', 'id');
    }

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_id');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    // --- Helper opsional ---

    public function isKredit(): bool
    {
        return ! is_null($this->piutang_id);
    }

    public function isTunai(): bool
    {
        return ! is_null($this->penjualan_id) && is_null($this->piutang_id);
    }
    // app/Models/Pembayaran.php

    protected $appends = ['jumlah_tampil']; // optional kalau mau ikut di toArray()

    public function getJumlahTampilAttribute(): float
    {
        if ($this->jenis === 'tunai') {
            return (float) $this->jumlah_bayar;
        }

        return (float) ($this->total_setelah_diskon ?? $this->jumlah_bayar);
    }
    public function bankAkun()
    {
        return $this->belongsTo(\App\Models\DaftarAkun::class, 'bank_akun_id');
    }

    protected static function booted()
{
    static::saved(function (Pembayaran $pembayaran) {
        if ($pembayaran->status !== 'lunas') {
            return;
        }

        // Ambil faktur yg dibayar
        $penjualan = $pembayaran->penjualan; // pastikan relasi belongsTo Penjualan
        if (! $penjualan) {
            return;
        }

        // Update hanya piutang untuk faktur ini
        Piutang::where('no_faktur', $penjualan->no_faktur)->update([
            'status' => 'lunas',
            'sisa_piutang' => 0,
            'tanggal_lunas' => $pembayaran->tanggal_bayar,
        ]);
    });
}

    public function jurnalUmum()
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_umum_id');
    }
}
