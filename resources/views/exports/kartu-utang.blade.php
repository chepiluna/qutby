<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 5px;
            letter-spacing: .02em;
        }

        .company {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .meta {
            font-size: 11px;
            color: #374151;
            margin-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #6b7280;
            padding: 8px 9px;
            vertical-align: middle;
        }

        th {
            background: #e5e7eb;
            text-align: center;
            font-weight: 700;
        }

        tfoot td {
            background: #e5e7eb;
            font-weight: 700;
        }

        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARTU UTANG</h1>
        <div class="company">CV QUTBY COLLECTION</div>
        <div class="meta"><strong>Nama Kreditor:</strong> {{ $vendor->nama_vendor }}</div>
        <div class="meta"><strong>Nomer Rekening:</strong> {{ $nomorRekening }}</div>
        <div class="meta"><strong>Periode:</strong> {{ $periode }}</div>
    </div>

    <table>
        <colgroup>
            <col style="width: 13%;">
            <col style="width: 35%;">
            <col style="width: 17%;">
            <col style="width: 10%;">
            <col style="width: 12%;">
            <col style="width: 15%;">
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Ref</th>
                <th>Debet</th>
                <th>Kredit</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">Awal Periode</td>
                <td>Saldo Awal</td>
                <td></td>
                <td class="right">-</td>
                <td class="right">-</td>
                <td class="right bold">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
            </tr>
            @forelse ($rows as $row)
                <tr>
                    <td class="center">{{ $row['tanggal'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                    <td class="center">{{ $row['ref'] }}</td>
                    <td class="right">{{ $row['debet'] ? 'Rp ' . number_format($row['debet'], 0, ',', '.') : '-' }}</td>
                    <td class="right">{{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}</td>
                    <td class="right bold">{{ $row['saldo'] ? 'Rp ' . number_format($row['saldo'], 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada transaksi di bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">Saldo Akhir</td>
                <td class="right bold">{{ $saldoAkhir ? 'Rp ' . number_format($saldoAkhir, 0, ',', '.') : '-' }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
