<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Pembantu Piutang</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 2px;
        }

        .sub-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .customer {
            margin-top: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        td {
            padding: 5px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .saldo {
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>BUKU PEMBANTU PIUTANG</h2>
<div class="sub-title">CV QUTBY COLLECTION</div>

@foreach($laporan as $item)

    <div class="customer">
        Pelanggan: {{ $item['customer'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Ref</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
            </tr>
        </thead>

        <tbody>
            @foreach($item['data'] as $row)
                <tr>
                    <td class="center">
                        {{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}
                    </td>
                    <td class="center">
                        {{ $row['ref'] }}
                    </td>
                    <td class="right">
                        {{ $row['debit'] > 0 ? number_format($row['debit'],0,',','.') : '-' }}
                    </td>
                    <td class="right">
                        {{ $row['kredit'] > 0 ? number_format($row['kredit'],0,',','.') : '-' }}
                    </td>
                    <td class="right saldo">
                        {{ number_format($row['saldo'],0,',','.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endforeach

</body>
</html>