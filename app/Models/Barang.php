<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// tambahan
class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function generateNextKodeBarang(): string
    {
        $lastNumber = static::query()
            ->where('kode_barang', 'like', 'BRG%')
            ->pluck('kode_barang')
            ->map(fn (?string $code): int => (int) preg_replace('/\D/', '', (string) $code))
            ->max() ?? 0;

        $nextNumber = $lastNumber + 1;

        return 'BRG' . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
    }

    // Dengan mutator ini, setiap kali data harga_barang dikirim ke database, koma akan otomatis dihapus.
    public function setHargaBarangAttribute($value)
    {
        // Hapus koma (,) dari nilai sebelum menyimpannya ke database
        $this->attributes['harga_barang'] = str_replace('.', '', $value);
    }
    
    public function penjualanBarang()
    {
        return $this->hasMany(PenjualanBarang::class, 'barang_id');
    }

    public function penjualanDetails(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'barang_id');
    }

    public function hasTransactionHistory(): bool
    {
        return $this->penjualanDetails()->exists();
    }

     public function stokBarang()
    {
        return $this->hasMany(StokBarang::class, 'barang_id');
    }

    public function kurangiStok(int $qty): void
    {
        if ($this->stok < $qty) {
            throw new \Exception("Stok {$this->nama_barang} tidak cukup! (Tersedia: {$this->stok}, Diminta: {$qty})");
        }
        $this->decrement('stok', $qty);
    }

}
