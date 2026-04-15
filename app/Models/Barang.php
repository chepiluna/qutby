<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// tambahan
use Illuminate\Support\Facades\DB;

class barang extends Model
{
    use HasFactory;

    protected $table = 'barang'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function generateNextKodeBarang(): string
    {
        $last = static::query()
            ->where('kode_barang', 'like', 'BRG-%')
            ->orderByDesc('id')
            ->value('kode_barang');

        $lastNumber = $last ? (int) substr($last, 4) : 0;
        $nextNumber = $lastNumber + 1;

        return 'BRG-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
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

