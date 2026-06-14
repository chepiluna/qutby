<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Pembantu Piutang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }

        .header .company {
            font-size: 12px;
            margin-top: 4px;
            font-weight: 600;
        }

        .account-card {
            border: 1px solid #ddd;
            margin-bottom: 18px;
        }

        .account-header {
            background: #A91323;
            color: #fff;
            padding: 10px 12px;
            font-weight: bold;
        }

        .account-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .account-header td {
            color: #fff;
            padding: 0;
            border: 0;
            font-size: 10px;
        }

        .account-header .customer {
            font-size: 11px;
            font-weight: bold;
        }

        .account-header .summary {
            text-align: right;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            border-bottom: 1px solid #000;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            background: #f5f5f5;
        }

        td {
            padding: 6px 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .small {
            font-size: 10px;
        }

        .footer-row {
            background: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKU PEMBANTU PIUTANG</h1>
        <div class="company">CV QUTBY COLLECTION</div>
    </div>

    @forelse($laporan as $index => $item)
        <div class="account-card">
            <div class="account-header">
                <table>
                    <tr>
                        <td class="customer">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} - {{ $item['customer'] }}
                        </td>
                        <td class="summary">
                            Status: {{ $item['status'] }}
                            &nbsp;|&nbsp;
                            Saldo Akhir: Rp {{ number_format($item['saldo_akhir'], 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Ref</th>
                        <th class="right">Debit</th>
                        <th class="right">Kredit</th>
                        <th class="right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['data'] as $row)
                        <tr>
                            <td class="small">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td class="small">{{ $row['keterangan'] }}</td>
                            <td class="small">{{ $row['ref'] }}</td>
                            <td class="small right">{{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}</td>
                            <td class="small right">{{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}</td>
                            <td class="small right">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr class="footer-row">
                        <td colspan="3" class="right">Total</td>
                        <td class="right">Rp {{ number_format($item['total_debit'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item['total_kredit'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div class="small">Tidak ada data untuk dicetak.</div>
    @endforelse
</body>
</html>
