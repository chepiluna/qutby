<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoTermin extends Model
{
    protected $table = 'po_termins';

    protected $fillable = [
        'pembelian_id',
        'termin_ke',
        'due_date',
        'nominal',
        'status',
        'tanggal_bayar',
    ];

    protected $casts = [
        'due_date' => 'date',
        'tanggal_bayar' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }
}
