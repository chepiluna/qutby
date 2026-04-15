<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Piutang;
use App\Models\Penjualan;
use App\Models\Pembayaran;

class Pelanggan extends Model
{
    use HasFactory;

    // tabel & primary key
    protected $table = 'pelanggan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'alamat',
        'no_telp',
    ];

    /**
     * Generate kode_pelanggan baru dengan format PLG000, PLG001, dst.
     */
    public static function generateNextKodePelanggan(): string
    {
        $last = static::query()
            ->where('kode_pelanggan', 'like', 'PLG-%')
            ->orderByDesc('id')
            ->value('kode_pelanggan');

        $lastNumber = $last ? (int) substr($last, 4) : 0;
        $nextNumber = $lastNumber + 1;

        return 'PLG-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // Relasi: satu pelanggan banyak piutang
    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'pelanggan_id', 'id');
    }

    // Relasi: satu pelanggan banyak penjualan
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id', 'id');
    }

    // Relasi: satu pelanggan banyak pembayaran
    public function pembayaran()
    {
        // foreign key di pembayaran = customer_id, owner key di sini = id
        return $this->hasMany(Pembayaran::class, 'customer_id', 'id');
    }
}
