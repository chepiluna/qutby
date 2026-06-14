@php
    $bulan = data_get($this->data, 'bulan');
    $tahun = data_get($this->data, 'tahun');

    $periodeText = '-';

    if ($bulan && $tahun) {
        $periodeText = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')
            ->translatedFormat('F Y');
    }
@endphp

<x-filament::page>
    <style>
        .ledger-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .ledger-card__header {
            align-items: center;
            background: #991b1b;
            color: #ffffff;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            justify-content: space-between;
            padding: 14px 18px;
        }

        .ledger-card__title {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
        }

        .ledger-card__summary {
            font-size: 12px;
            line-height: 1.4;
            opacity: .94;
        }

        .ledger-table-wrap {
            overflow-x: auto;
        }

        .ledger-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1120px;
            table-layout: fixed;
            width: 100%;
        }

        .ledger-table th {
            background: #fee2e2;
            border-bottom: 1px solid #fecaca;
            color: #991b1b;
            font-size: 13px;
            font-weight: 800;
            height: 48px;
            padding: 8px 14px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .ledger-table thead tr:last-child th {
            height: 38px;
        }

        .ledger-table th + th,
        .ledger-table td + td {
            border-left: 1px solid #f3f4f6;
        }

        .ledger-table tbody td {
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
            font-size: 13px;
            padding: 11px 14px;
            vertical-align: middle;
        }

        .ledger-table tbody tr:hover td {
            background: #fff7f7;
        }

        .ledger-date,
        .ledger-ref,
        .ledger-money {
            white-space: nowrap;
        }

        .ledger-desc {
            overflow-wrap: anywhere;
        }

        .ledger-money {
            font-variant-numeric: tabular-nums;
            text-align: right;
        }

        .ledger-balance {
            background: #fffafa;
            font-weight: 800;
        }

        .ledger-balance-start {
            border-left: 1px solid #fecaca !important;
            border-left-color: #fecaca !important;
        }

        .ledger-row-muted td {
            background: #f9fafb;
            font-weight: 700;
        }

        .ledger-row-total td {
            background: #f3f4f6;
            border-bottom: 0;
            font-weight: 900;
        }

        .ledger-empty {
            color: #6b7280 !important;
            padding: 18px !important;
            text-align: center;
        }
    </style>

    {{-- HEADER --}}
    <div class="mb-0 relative rounded-xl border border-gray-200 bg-white p-6">
        <div class="text-center">
            <div class="text-xl font-bold">CV.QUTBY CREATIVINDO</div>
            <div class="text-lg font-semibold tracking-wide">LAPORAN BUKU BESAR</div>
            <div class="text-sm text-gray-600">PERIODE: {{ $periodeText }}</div>
        </div>
    </div>

    <div class="mb-0 rounded-xl border border-gray-200 bg-white p-4">
        {{ $this->form }}
        <x-filament-actions::modals />
    </div>

    {{-- HASIL --}}
    @forelse ($this->ledgers as $ledger)
        @php
            $akun = $ledger['akun'];
            $saldoAkhir = collect($ledger['rows'])->last()['saldo'] ?? $ledger['saldo_awal'];
            $saldoColumns = function (float|int $saldo) use ($ledger): array {
                $normalSide = $ledger['normal_side'] ?? 'debit';
                $amount = abs((float) $saldo);

                if ((float) $saldo === 0.0) {
                    return ['debit' => 0.0, 'kredit' => 0.0];
                }

                if ($normalSide === 'debit') {
                    return $saldo > 0
                        ? ['debit' => $amount, 'kredit' => 0.0]
                        : ['debit' => 0.0, 'kredit' => $amount];
                }

                return $saldo > 0
                    ? ['debit' => 0.0, 'kredit' => $amount]
                    : ['debit' => $amount, 'kredit' => 0.0];
            };
            $formatSaldo = fn (float|int $value): string => $value ? 'Rp ' . number_format($value, 0, ',', '.') : '-';
            $saldoAwalColumns = $saldoColumns($ledger['saldo_awal']);
            $saldoAkhirColumns = $saldoColumns($saldoAkhir);
        @endphp

        <div class="ledger-card mb-6">

            {{-- HEADER MAROON --}}
            <div class="ledger-card__header">
                <div class="ledger-card__title">
                    {{ $akun->kode_akun ?? '-' }} — {{ $akun->nama_akun ?? '-' }}
                </div>
                <div class="ledger-card__summary">
                    Saldo Awal: <strong>Rp {{ number_format($ledger['saldo_awal'], 0, ',', '.') }}</strong>
                    &nbsp; | &nbsp;
                    Saldo Akhir: <strong>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="ledger-table-wrap bg-white">
                <table class="ledger-table">
                    <colgroup>
                        <col style="width: 12%">
                        <col style="width: 31%">
                        <col style="width: 9%">
                        <col style="width: 12%">
                        <col style="width: 12%">
                        <col style="width: 12%">
                        <col style="width: 12%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-left align-middle">Tanggal</th>
                            <th rowspan="2" class="text-left align-middle">Keterangan</th>
                            <th rowspan="2" class="text-left align-middle">Ref</th>
                            <th rowspan="2" class="text-right align-middle">Debit</th>
                            <th rowspan="2" class="text-right align-middle">Kredit</th>
                            <th colspan="2" class="text-center ledger-balance-start">Saldo</th>
                        </tr>
                        <tr>
                            <th class="text-right ledger-balance-start">Debit (Rp)</th>
                            <th class="text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- SALDO AWAL --}}
                        <tr class="ledger-row-muted">
                            <td></td>
                            <td class="ledger-desc">Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="ledger-money ledger-balance ledger-balance-start">
                                {{ $formatSaldo($saldoAwalColumns['debit']) }}
                            </td>
                            <td class="ledger-money ledger-balance">
                                {{ $formatSaldo($saldoAwalColumns['kredit']) }}
                            </td>
                        </tr>

                        {{-- TRANSAKSI --}}
                        @forelse ($ledger['rows'] as $row)
                            @php
                                $rowSaldoColumns = $saldoColumns($row['saldo']);
                            @endphp
                            <tr>
                                <td class="ledger-date">
                                    {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="ledger-desc">{{ $row['keterangan'] }}</td>
                                <td class="ledger-ref">{{ $row['ref'] }}</td>

                                {{-- DEBIT (HITAM) --}}
                                <td class="ledger-money">
                                    {{ $row['debit'] ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- KREDIT (HITAM) --}}
                                <td class="ledger-money">
                                    {{ $row['kredit'] ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- SALDO (BOLD HITAM) --}}
                                <td class="ledger-money ledger-balance ledger-balance-start">
                                    {{ $formatSaldo($rowSaldoColumns['debit']) }}
                                </td>
                                <td class="ledger-money ledger-balance">
                                    {{ $formatSaldo($rowSaldoColumns['kredit']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="ledger-empty">
                                    Tidak ada transaksi bulan ini
                                </td>
                            </tr>
                        @endforelse

                        {{-- SALDO AKHIR --}}
                        <tr class="ledger-row-total">
                            <td colspan="5" class="ledger-money">Saldo Akhir</td>
                            <td class="ledger-money ledger-balance ledger-balance-start">
                                {{ $formatSaldo($saldoAkhirColumns['debit']) }}
                            </td>
                            <td class="ledger-money ledger-balance">
                                {{ $formatSaldo($saldoAkhirColumns['kredit']) }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    @empty
        <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-500">
            Pilih bulan dan tahun terlebih dahulu.
        </div>
    @endforelse
</x-filament::page>
