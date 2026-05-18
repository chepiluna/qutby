<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Besar</title>
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

        .header .periode {
            font-size: 11px;
            margin-top: 4px;
            color: #555;
        }

        .header .filter {
            font-size: 11px;
            margin-top: 2px;
            color: #555;
        }

        .account-card {
            border: 1px solid #ddd;
            margin-bottom: 18px;
        }

        .account-header {
            background: #A91323;
            color: #fff;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .account-header .saldo {
            font-size: 10px;
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
        <h1>LAPORAN BUKU BESAR</h1>
        <div class="company">CV QUTBY COLLECTION</div>
        <div class="periode">PERIODE: {{ $periode }}</div>
        @if(isset($akun) && $akun)
            <div class="filter">Akun: {{ $akun->kode_akun }} — {{ $akun->nama_akun }}</div>
        @endif
    </div>

    @forelse($ledgers as $ledger)
        @php
            $akun = $ledger['akun'];
            $saldoAkhir = collect($ledger['rows'])->last()['saldo'] ?? $ledger['saldo_awal'];
            $saldoLabel = ($ledger['normal_side'] ?? 'debit') === 'debit' ? 'Saldo (D)' : 'Saldo (K)';
        @endphp

        <div class="account-card">
            <div class="account-header">
                <div>{{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? '-' }}</div>
                <div class="saldo">
                    Saldo Awal: Rp {{ number_format($ledger['saldo_awal'], 0, ',', '.') }}
                    &nbsp;|&nbsp;
                    Saldo Akhir: Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Ref</th>
                        <th class="right">Debit</th>
                        <th class="right">Kredit</th>
                        <th class="right">{{ $saldoLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td class="small">Saldo Awal</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="right">Rp {{ number_format($ledger['saldo_awal'], 0, ',', '.') }}</td>
                    </tr>

                    @forelse($ledger['rows'] as $row)
                        <tr>
                            <td class="small">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td class="small">{{ $row['keterangan'] }}</td>
                            <td class="small">{{ $row['ref'] }}</td>
                            <td class="small right">{{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}</td>
                            <td class="small right">{{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}</td>
                            <td class="small right">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center small">Tidak ada transaksi bulan ini</td>
                        </tr>
                    @endforelse

                    <tr class="footer-row">
                        <td colspan="5" class="right">Saldo Akhir</td>
                        <td class="right">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div class="small">Tidak ada data untuk dicetak.</div>
    @endforelse
</body>
</html>
