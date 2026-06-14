<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jurnal Umum</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .kredit-indent {
            padding-left: 30px;
            text-align: center;
        }

        .header {
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 12px;
            margin: 0;
            font-weight: bold;
        }

        .header h2 {
            font-size: 18px;
            margin: 4px 0;
            font-weight: bold;
        }

        .header p {
            font-size: 11px;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #8B0000;
            color: #ffffff;
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    {{-- HEADER LAPORAN --}}
    <div class="header text-center">
        <h2>CV.QUTBY CREATIVINDO</h2>
        <h1>LAPORAN JURNAL UMUM</h1>
        <p>Periode: {{ $periode ?? 'Semua Periode' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 35%">Keterangan</th>
                <th style="width: 10%">No Akun</th>
                <th style="width: 20%">Debit</th>
                <th style="width: 20%">Kredit</th>
            </tr>
        </thead>

        <tbody>

            @php
                $totalDebit = 0;
                $totalKredit = 0;
            @endphp

            @forelse ($rows as $row)

                @php
                    if ($row->posisi === 'debit') {
                        $totalDebit += $row->nominal;
                    } else {
                        $totalKredit += $row->nominal;
                    }
                @endphp

                <tr>

                    {{-- TANGGAL --}}
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($row->jurnalUmum->tanggal)->format('d-m-Y') }}
                    </td>

                    {{-- KETERANGAN --}}
                    <td class="{{ $row->posisi === 'kredit' ? 'kredit-indent' : '' }}">
                        {{ $row->akun->nama_akun }}
                    </td>

                    {{-- NO AKUN --}}
                    <td class="text-center">
                        {{ $row->akun->kode_akun }}
                    </td>

                    {{-- DEBIT --}}
                    <td class="text-right">
                        @if ($row->posisi === 'debit')
                            Rp {{ number_format($row->nominal, 0, ',', '.') }}
                        @endif
                    </td>

                    {{-- KREDIT --}}
                    <td class="text-right">
                        @if ($row->posisi === 'kredit')
                            Rp {{ number_format($row->nominal, 0, ',', '.') }}
                        @endif
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="text-center">
                        Data tidak ditemukan
                    </td>
                </tr>

            @endforelse

            {{-- TOTAL --}}
            <tr class="total-row">
                <td colspan="3" class="text-center">
                    TOTAL
                </td>

                <td class="text-right">
                    Rp {{ number_format($totalDebit, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($totalKredit, 0, ',', '.') }}
                </td>
            </tr>

        </tbody>
    </table>

</body>
</html>
