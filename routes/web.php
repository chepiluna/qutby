<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/logout', function () {
    $role = Auth::user()?->role;
    
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    
    return match($role) {
        'finance' => redirect('/finance/login'),
        default   => redirect('/operasional/login'),
    };
})->name('logout')->middleware('web');

Route::get('/kartu-utang/pdf', function () {
    abort_unless(Auth::check() && in_array(Auth::user()?->role, ['admin', 'finance'], true), 403);

    $bulan = request('bulan', now()->format('m'));
    $tahun = request('tahun', now()->format('Y'));
    $vendorId = request('vendor_id');
    $vendor = $vendorId ? Vendor::find($vendorId) : null;

    abort_unless($vendor, 404);

    $tglMulai = Carbon::createFromDate((int) $tahun, (int) $bulan, 1)->startOfMonth();
    $tglAkhir = $tglMulai->copy()->endOfMonth();

    $saldoAwal = Pembelian::where('vendor_id', $vendor->id)
        ->where('tanggal', '<', $tglMulai->format('Y-m-d'))
        ->sum('total_akhir');

    if ((float) $saldoAwal === 0.0) {
        $saldoAwal = 500000;
    }

    $mutasi = Pembelian::where('vendor_id', $vendor->id)
        ->whereBetween('tanggal', [$tglMulai->format('Y-m-d'), $tglAkhir->format('Y-m-d')])
        ->orderBy('tanggal')
        ->get()
        ->map(fn (Pembelian $p): array => [
            'tanggal' => Carbon::parse($p->tanggal)->translatedFormat('d F Y'),
            'keterangan' => 'Pembelian dari ' . ($vendor->nama_vendor ?? '-'),
            'ref' => $p->nomor,
            'debet' => 0,
            'kredit' => (float) $p->total_akhir,
        ]);

    $running = (float) $saldoAwal;
    $rows = $mutasi->map(function (array $row) use (&$running): array {
        $running += $row['kredit'] - $row['debet'];
        $row['saldo'] = $running;

        return $row;
    });

    $pdf = Pdf::loadView('exports.kartu-utang', [
        'vendor' => $vendor,
        'periode' => $tglMulai->translatedFormat('F Y'),
        'nomorRekening' => $vendor->nomor_rekening ?: '-',
        'saldoAwal' => (float) $saldoAwal,
        'rows' => $rows,
        'saldoAkhir' => $running,
    ])->setPaper('a4', 'landscape');

    return request()->boolean('download')
        ? $pdf->download('kartu-utang-' . $vendor->id . '-' . $tahun . '-' . $bulan . '.pdf')
        : $pdf->stream('kartu-utang.pdf');
})->name('kartu-utang.pdf')->middleware('web');

Route::get('/laporan-pembelian/pdf', function () {
    abort_unless(Auth::check() && in_array(Auth::user()?->role, ['admin', 'finance', 'operasional', 'sales'], true), 403);

    $bulan = request('bulan', now()->format('m'));
    $tahun = request('tahun', now()->format('Y'));
    $vendorId = request('vendor_id');

    $rows = getLaporanPembelianRows($bulan, $tahun, $vendorId);
    $periode = getLaporanPembelianPeriode($bulan, $tahun);
    $grandTotal = $rows->sum('total');

    $pdf = Pdf::loadView('exports.laporan-pembelian', compact('rows', 'periode', 'grandTotal'))
        ->setPaper('a4', 'landscape');

    return $pdf->stream('laporan-pembelian-' . $tahun . '-' . $bulan . '.pdf');
})->name('laporan-pembelian.pdf')->middleware('web');

Route::get('/laporan-pembelian/excel', function () {
    abort_unless(Auth::check() && in_array(Auth::user()?->role, ['admin', 'finance', 'operasional', 'sales'], true), 403);

    $bulan = request('bulan', now()->format('m'));
    $tahun = request('tahun', now()->format('Y'));
    $vendorId = request('vendor_id');

    $rows = getLaporanPembelianRows($bulan, $tahun, $vendorId);
    $periode = getLaporanPembelianPeriode($bulan, $tahun);
    $grandTotal = $rows->sum('total');
    $filename = 'laporan-pembelian-' . $tahun . '-' . $bulan . '.xls';

    return response()
        ->view('exports.laporan-pembelian', compact('rows', 'periode', 'grandTotal'))
        ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
})->name('laporan-pembelian.excel')->middleware('web');

function getLaporanPembelianRows(string $bulan, string $tahun, mixed $vendorId): \Illuminate\Support\Collection
{
    return PembelianDetail::with([
            'barang',
            'pembelian.vendor',
        ])
        ->whereHas('pembelian', function ($query) use ($bulan, $tahun, $vendorId) {
            if ($bulan && $tahun) {
                $query->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            }

            if ($vendorId) {
                $query->where('vendor_id', $vendorId);
            }

            $query->where('status', '!=', 'dibatalkan');
        })
        ->get()
        ->filter(fn (PembelianDetail $detail) => $detail->pembelian)
        ->sortBy(fn (PembelianDetail $detail) => Carbon::parse($detail->pembelian->tanggal)->format('Y-m-d') . '-' . str_pad((string) $detail->id, 10, '0', STR_PAD_LEFT))
        ->map(function (PembelianDetail $detail) {
            $hargaSatuan = (float) ($detail->harga_satuan ?? $detail->harga ?? $detail->hpp ?? 0);
            $jumlah = (int) ($detail->qty ?? 0);

            return [
                'tanggal' => Carbon::parse($detail->pembelian->tanggal),
                'nomor_po' => $detail->pembelian->nomor ?? '-',
                'vendor' => $detail->pembelian->vendor_manual
                    ?: ($detail->pembelian->vendor->nama_vendor ?? '-'),
                'nama_barang' => $detail->barang->nama_barang ?? '-',
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaSatuan,
                'total' => $jumlah * $hargaSatuan,
            ];
        })
        ->values();
}

function getLaporanPembelianPeriode(string $bulan, string $tahun): string
{
    $bulanNama = [
        '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',    '05' => 'Mei',      '06' => 'Juni',
        '07' => 'Juli',     '08' => 'Agustus',  '09' => 'September',
        '10' => 'Oktober',  '11' => 'November', '12' => 'Desember',
    ];

    return ($bulanNama[$bulan] ?? '-') . ' ' . $tahun;
}
