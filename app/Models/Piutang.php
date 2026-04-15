<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Piutang extends Model
{
    use HasFactory;

    // Tabel tanpa "s"
    protected $table = 'piutang';

    protected $guarded = [];

    protected $casts = [
        'tanggal_faktur'  => 'date',
        'tanggal_lunas'   => 'date',
        'tgl_jatuh_tempo' => 'date',
        'created_at'      => 'datetime',
    ];

    // === Relasi ===

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function termin(): BelongsTo
    {
        return $this->belongsTo(TerminPembayaran::class, 'termin_id');
    }

    // optional, kalau nanti ada tabel penjualan
    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    // === Scope & helper ===

    public function scopeBelumLunas(Builder $query): Builder
    {
        return $query->where('status', 'belum_lunas');
    }

    public function hitungSisa(): float
    {
        return (float) $this->sisa_piutang;
    }

    /**
     * Tanggal dasar untuk hitung termin.
     * Prioritas pakai tanggal_faktur; kalau kosong pakai created_at.
     */
    protected function getBaseTanggalTermin(): ?Carbon
    {
        if ($this->tanggal_faktur instanceof Carbon) {
            return $this->tanggal_faktur->copy();
        }

        if ($this->created_at instanceof Carbon) {
            return $this->created_at->copy();
        }

        return null;
    }

    // === Termin 2/10, n/30 ===

    public function getTanggalDiskonSampaiAttribute(): ?Carbon
    {
        if (! $this->hari_diskon) {
            return null;
        }

        $base = $this->getBaseTanggalTermin();

        return $base?->addDays($this->hari_diskon);
    }

    public function getTanggalJatuhTempoAttribute(): ?Carbon
    {
        if (! $this->hari_jatuh_tempo) {
            return null;
        }

        $base = $this->getBaseTanggalTermin();

        return $base?->addDays($this->hari_jatuh_tempo);
    }

    /**
     * Hitung berapa yang harus dibayar kalau dilunasi di $tanggalBayar,
     * dengan aturan termin (misal 2/10, n/30).
     */
    public function hitungJumlahBayar(Carbon $tanggalBayar): float
    {
        $total = (float) $this->total_piutang;

        $base = $this->getBaseTanggalTermin();

        if (
            $this->diskon_persen > 0 &&
            $this->hari_diskon &&
            $base &&
            $tanggalBayar->lte($base->copy()->addDays($this->hari_diskon))
        ) {
            // masih dalam periode diskon -> potong diskon_persen
            $total *= (1 - $this->diskon_persen / 100);
        }

        return $total;
    }

    /**
     * Dipanggil dari tabel Pembayaran:
     * - isi tanggal_lunas
     * - set sisa_piutang = 0
     * - ubah status jadi 'lunas'
     */
    public function tandaiLunasDariPembayaran(Carbon $tanggalBayar): void
    {
        $this->tanggal_lunas = $tanggalBayar;
        $this->sisa_piutang  = 0;
        $this->status        = 'lunas'; // sesuaikan dengan nilai yang kamu pakai di DB

        $this->save();
    }
    
}
