<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 3px; text-transform: uppercase; }
        p { margin: 0 0 4px; }
        .report-header { margin-bottom: 18px; text-align: center; }
        .company-name { font-size: 12px; font-weight: bold; margin-bottom: 3px; }
        .period { font-size: 11px; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        thead { display: table-header-group; }
        th, td { border: 1px solid #6b7280; padding: 6px; }
        th { background: #e5e7eb; text-align: center; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Laporan Pembelian</h1>
        <div class="company-name">CV QUTBY COLLECTION</div>
        <div class="period">PERIODE: {{ $periode }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal Pembelian</th>
                <th>No. PO</th>
                <th>Vendor</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="center">{{ $row['tanggal']->format('d/m/Y') }}</td>
                    <td class="center">{{ $row['nomor_po'] }}</td>
                    <td>{{ $row['vendor'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td>
                    <td class="right">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['harga_satuan'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data pesanan pembelian pada periode {{ $periode }}.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="right">Total Pembelian Bulanan</th>
                <th class="right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
