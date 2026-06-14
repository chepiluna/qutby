<x-filament-panels::page>
    @php
        $data = $this->getDashboardData();
        $fmtRp = fn (float|int $value): string => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $fmtQty = fn (float|int $value): string => number_format((float) $value, 0, ',', '.');
        $trend = $data['trend'];
        $sales = $data['sales'];
        $purchases = $data['purchases'];
        $grossProfit = $data['grossProfit'];
        $trendMax = max(1, ...array_map('floatval', $trend['sales']), ...array_map('floatval', $trend['purchases']));
    @endphp

    <style>
        :root {
            --dash-sale: #D85A30;
            --dash-sale-soft: #fff0ea;
            --dash-buy: #185FA5;
            --dash-buy-soft: #eaf3ff;
            --dash-success: #15803d;
            --dash-success-soft: #dcfce7;
            --dash-warning: #b45309;
            --dash-warning-soft: #fef3c7;
            --dash-danger: #b91c1c;
            --dash-danger-soft: #fee2e2;
            --dash-ink: #0f172a;
            --dash-muted: #64748b;
            --dash-line: #d9dee8;
            --dash-surface: #ffffff;
            --dash-page: #f8fafc;
        }

        .dark {
            --dash-sale-soft: rgba(216, 90, 48, 0.18);
            --dash-buy-soft: rgba(24, 95, 165, 0.20);
            --dash-success-soft: rgba(21, 128, 61, 0.22);
            --dash-warning-soft: rgba(180, 83, 9, 0.22);
            --dash-danger-soft: rgba(185, 28, 28, 0.22);
            --dash-ink: #f8fafc;
            --dash-muted: #cbd5e1;
            --dash-line: #334155;
            --dash-surface: #111827;
            --dash-page: #020617;
        }

        .ops-dashboard {
            color: var(--dash-ink);
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .ops-dashboard * {
            box-sizing: border-box;
        }

        .ops-header,
        .ops-card,
        .ops-profit,
        .ops-panel {
            background: var(--dash-surface);
            border: 1px solid var(--dash-line);
            border-radius: 8px;
        }

        .ops-header {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 18px 20px;
        }

        .ops-header h1 {
            color: var(--dash-ink);
            font-size: 22px;
            font-weight: 800;
            line-height: 1.15;
            margin: 0;
        }

        .ops-header p,
        .ops-card__label,
        .ops-panel__sub {
            color: var(--dash-muted);
            font-size: 12px;
            margin: 0;
        }

        .ops-badge {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            gap: 6px;
            min-height: 26px;
            padding: 4px 10px;
            white-space: nowrap;
        }

        .ops-badge--success {
            background: var(--dash-success-soft);
            color: #15803d;
        }

        .ops-badge--warning {
            background: var(--dash-warning-soft);
            color: #b45309;
        }

        .ops-badge--danger {
            background: var(--dash-danger-soft);
            color: #b91c1c;
        }

        .ops-section {
            display: grid;
            gap: 10px;
        }

        .ops-section__title {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .ops-section__title h2 {
            color: var(--dash-ink);
            font-size: 14px;
            font-weight: 800;
            margin: 0;
        }

        .ops-metrics {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(140px, 1fr));
        }

        .ops-card {
            min-height: 112px;
            padding: 14px;
        }

        .ops-card--sales {
            background: linear-gradient(180deg, var(--dash-sale-soft), var(--dash-surface));
        }

        .ops-card--purchase {
            background: linear-gradient(180deg, var(--dash-buy-soft), var(--dash-surface));
        }

        .ops-card__value {
            color: var(--dash-ink);
            font-size: 18px;
            font-weight: 900;
            line-height: 1.2;
            margin-top: 9px;
            overflow-wrap: anywhere;
        }

        .ops-card__meta {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .ops-percent {
            color: var(--dash-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .ops-profit {
            align-items: center;
            background: linear-gradient(90deg, #dcfce7, #f0fdf4);
            border-color: #86efac;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 18px 20px;
        }

        .dark .ops-profit {
            background: linear-gradient(90deg, rgba(21, 128, 61, 0.32), rgba(22, 101, 52, 0.18));
            border-color: rgba(134, 239, 172, 0.35);
        }

        .ops-profit h2 {
            color: #14532d;
            font-size: 15px;
            font-weight: 900;
            margin: 0 0 4px;
        }

        .dark .ops-profit h2,
        .dark .ops-profit strong {
            color: #bbf7d0;
        }

        .ops-profit p {
            color: #166534;
            font-size: 12px;
            margin: 0;
        }

        .dark .ops-profit p {
            color: #dcfce7;
        }

        .ops-profit strong {
            color: #14532d;
            display: block;
            font-size: 24px;
            font-weight: 950;
            line-height: 1.1;
            text-align: right;
        }

        .ops-profit span {
            color: #166534;
            display: block;
            font-size: 12px;
            font-weight: 800;
            margin-top: 5px;
            text-align: right;
        }

        .dark .ops-profit span {
            color: #dcfce7;
        }

        .ops-bottom-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(360px, 2fr) minmax(220px, 1fr) minmax(240px, 1fr);
        }

        .ops-panel {
            min-width: 0;
            padding: 16px;
        }

        .ops-panel--stack {
            align-content: start;
            display: grid;
            gap: 22px;
        }

        .ops-panel--stack > div {
            min-width: 0;
        }

        .ops-panel__head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .ops-panel h3 {
            color: var(--dash-ink);
            font-size: 14px;
            font-weight: 900;
            margin: 0;
        }

        .ops-table-wrap {
            overflow-x: auto;
        }

        .ops-table {
            border-collapse: collapse;
            font-size: 12px;
            min-width: 100%;
            width: 100%;
        }

        .ops-table th {
            border-bottom: 1px solid var(--dash-line);
            color: var(--dash-muted);
            font-weight: 900;
            padding: 9px 6px;
            text-align: left;
            white-space: nowrap;
        }

        .ops-table td {
            border-bottom: 1px solid var(--dash-line);
            color: var(--dash-ink);
            font-weight: 600;
            padding: 10px 6px;
            vertical-align: middle;
        }

        .ops-table tr:last-child td {
            border-bottom: 0;
        }

        .ops-num {
            text-align: right !important;
            white-space: nowrap;
        }

        .ops-qty {
            text-align: center !important;
            white-space: nowrap;
        }

        .ops-empty {
            color: var(--dash-muted) !important;
            font-weight: 700 !important;
            text-align: center !important;
        }

        .ops-donut-box {
            display: grid;
            gap: 14px;
            justify-items: center;
        }

        .ops-donut-frame {
            aspect-ratio: 1;
            max-width: 220px;
            width: 100%;
        }

        .ops-donut {
            align-items: center;
            aspect-ratio: 1;
            background:
                radial-gradient(circle at center, var(--dash-surface) 0 58%, transparent 59%),
                conic-gradient(#fbbf24 0 calc(var(--cash-percent) * 1%), var(--dash-sale) 0 100%);
            border-radius: 999px;
            display: grid;
            justify-items: center;
            width: 100%;
        }

        .ops-donut__center {
            color: var(--dash-ink);
            display: grid;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            text-align: center;
        }

        .ops-donut__center strong {
            font-size: 22px;
            font-weight: 950;
        }

        .ops-legend {
            display: grid;
            gap: 8px;
            width: 100%;
        }

        .ops-legend__item {
            align-items: center;
            color: var(--dash-ink);
            display: grid;
            font-size: 12px;
            font-weight: 800;
            gap: 8px;
            grid-template-columns: auto 1fr auto;
        }

        .ops-dot {
            border-radius: 999px;
            display: inline-block;
            height: 10px;
            width: 10px;
        }

        .ops-dot--sale {
            background: var(--dash-sale);
        }

        .ops-dot--buy {
            background: var(--dash-buy);
        }

        .ops-dot--cash {
            background: #fbbf24;
        }

        .ops-dot--credit {
            background: var(--dash-sale);
        }

        .ops-chart-panel {
            padding: 16px;
        }

        .ops-chart-frame {
            height: 360px;
            position: relative;
            width: 100%;
        }

        .ops-trend {
            align-items: end;
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(30, minmax(12px, 1fr));
            height: 100%;
            overflow-x: auto;
            padding: 10px 0 24px;
        }

        .ops-trend__day {
            align-items: end;
            display: grid;
            gap: 4px;
            grid-template-columns: 1fr 1fr;
            height: 100%;
            min-width: 16px;
            position: relative;
        }

        .ops-trend__bar {
            border-radius: 4px 4px 0 0;
            min-height: 2px;
        }

        .ops-trend__bar--sale {
            background: var(--dash-sale);
            height: max(2px, calc(var(--sale-height) * 1%));
        }

        .ops-trend__bar--buy {
            background: var(--dash-buy);
            height: max(2px, calc(var(--buy-height) * 1%));
        }

        .ops-trend__label {
            bottom: -20px;
            color: var(--dash-muted);
            font-size: 10px;
            font-weight: 800;
            left: 50%;
            position: absolute;
            transform: translateX(-50%);
            white-space: nowrap;
        }

        .ops-chart-legend {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: center;
            margin-top: 12px;
        }

        @media (max-width: 1180px) {
            .ops-metrics {
                grid-template-columns: repeat(3, minmax(150px, 1fr));
            }

            .ops-bottom-grid {
                grid-template-columns: 1fr 1fr;
            }

            .ops-panel--stack:first-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 760px) {
            .ops-header,
            .ops-profit {
                align-items: flex-start;
                flex-direction: column;
            }

            .ops-profit strong,
            .ops-profit span {
                text-align: left;
            }

            .ops-metrics,
            .ops-bottom-grid {
                grid-template-columns: 1fr;
            }

            .ops-chart-frame {
                height: 300px;
            }

        }
    </style>

    <div class="ops-dashboard">
        <section class="ops-section">
            <div class="ops-section__title">
                <h2>Metrik Penjualan</h2>
            </div>
            <div class="ops-metrics">
                <article class="ops-card ops-card--sales">
                    <p class="ops-card__label">Total Penjualan Bulan Ini</p>
                    <div class="ops-card__value">{{ $fmtRp($sales['total']) }}</div>
                </article>
                <article class="ops-card ops-card--sales">
                    <p class="ops-card__label">Piutang Belum Lunas</p>
                    <div class="ops-card__value">{{ $fmtRp($sales['receivables']) }}</div>
                    <div class="ops-card__meta"><span class="ops-badge ops-badge--warning">Belum Lunas</span></div>
                </article>
                <article class="ops-card ops-card--sales">
                    <p class="ops-card__label">Barang Terjual</p>
                    <div class="ops-card__value">{{ $fmtQty($sales['qty']) }} qty</div>
                </article>
                <article class="ops-card ops-card--sales">
                    <p class="ops-card__label">Penjualan Tunai</p>
                    <div class="ops-card__value">{{ $fmtRp($sales['cash']) }}</div>
                    <div class="ops-card__meta"><span class="ops-percent">{{ $sales['cashPercent'] }}% dari total</span></div>
                </article>
                <article class="ops-card ops-card--sales">
                    <p class="ops-card__label">Penjualan Kredit</p>
                    <div class="ops-card__value">{{ $fmtRp($sales['credit']) }}</div>
                    <div class="ops-card__meta"><span class="ops-percent">{{ $sales['creditPercent'] }}% dari total</span></div>
                </article>
            </div>
        </section>

        <section class="ops-section">
            <div class="ops-section__title">
                <h2>Metrik Pembelian</h2>
            </div>
            <div class="ops-metrics">
                <article class="ops-card ops-card--purchase">
                    <p class="ops-card__label">Total Pembelian Bulan Ini</p>
                    <div class="ops-card__value">{{ $fmtRp($purchases['total']) }}</div>
                </article>
                <article class="ops-card ops-card--purchase">
                    <p class="ops-card__label">Hutang Belum Lunas</p>
                    <div class="ops-card__value">{{ $fmtRp($purchases['debt']) }}</div>
                    <div class="ops-card__meta"><span class="ops-badge ops-badge--danger">Belum Lunas</span></div>
                </article>
                <article class="ops-card ops-card--purchase">
                    <p class="ops-card__label">Barang Diterima</p>
                    <div class="ops-card__value">{{ $fmtQty($purchases['receivedQty']) }} qty</div>
                </article>
                <article class="ops-card ops-card--purchase">
                    <p class="ops-card__label">Pembelian Tunai</p>
                    <div class="ops-card__value">{{ $fmtRp($purchases['cash']) }}</div>
                    <div class="ops-card__meta"><span class="ops-percent">{{ $purchases['cashPercent'] }}% dari total</span></div>
                </article>
                <article class="ops-card ops-card--purchase">
                    <p class="ops-card__label">Pembelian Kredit</p>
                    <div class="ops-card__value">{{ $fmtRp($purchases['credit']) }}</div>
                    <div class="ops-card__meta"><span class="ops-percent">{{ $purchases['creditPercent'] }}% dari total</span></div>
                </article>
            </div>
        </section>

        <section class="ops-profit">
            <div>
                <h2>Laba Kotor</h2>
                <p>Total Penjualan - Total Pembelian</p>
            </div>
            <div>
                <strong>{{ $fmtRp($grossProfit['amount']) }}</strong>
                <span>Margin {{ number_format($grossProfit['margin'], 1, ',', '.') }}%</span>
            </div>
        </section>

        <section class="ops-bottom-grid">
            <div class="ops-panel ops-panel--stack">
                <div>
                    <div class="ops-panel__head">
                        <h3>Top Barang Terjual</h3>
                    </div>
                    <div class="ops-table-wrap">
                        <table class="ops-table">
                            <colgroup>
                                <col style="width: 52%;">
                                <col style="width: 16%;">
                                <col style="width: 32%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="ops-qty">Qty</th>
                                    <th class="ops-num">Omzet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data['topSold'] as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="ops-qty">{{ $fmtQty($row->qty) }}</td>
                                        <td class="ops-num">{{ $fmtRp($row->amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="ops-empty">Belum ada data penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="ops-panel__head">
                        <h3>Top Barang Dibeli</h3>
                    </div>
                    <div class="ops-table-wrap">
                        <table class="ops-table">
                            <colgroup>
                                <col style="width: 52%;">
                                <col style="width: 16%;">
                                <col style="width: 32%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="ops-qty">Qty</th>
                                    <th class="ops-num">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data['topBought'] as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="ops-qty">{{ $fmtQty($row->qty) }}</td>
                                        <td class="ops-num">{{ $fmtRp($row->amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="ops-empty">Belum ada data pembelian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ops-panel">
                <div class="ops-panel__head">
                    <div>
                        <h3>Tunai vs Kredit</h3>
                        <p class="ops-panel__sub">Penjualan bulan ini</p>
                    </div>
                </div>
                <div class="ops-donut-box">
                    <div class="ops-donut-frame">
                        <div class="ops-donut" style="--cash-percent: {{ $sales['cashPercent'] }};">
                            <div class="ops-donut__center">
                                <strong>{{ $sales['cashPercent'] }}%</strong>
                                <span>Tunai</span>
                            </div>
                        </div>
                    </div>
                    <div class="ops-legend">
                        <div class="ops-legend__item">
                            <span class="ops-dot ops-dot--cash"></span>
                            <span>Tunai</span>
                            <strong>{{ $fmtRp($sales['cash']) }}</strong>
                        </div>
                        <div class="ops-legend__item">
                            <span class="ops-dot ops-dot--credit"></span>
                            <span>Kredit</span>
                            <strong>{{ $fmtRp($sales['credit']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ops-panel ops-panel--stack">
                <div>
                    <div class="ops-panel__head">
                        <h3>Stok Menipis</h3>
                    </div>
                    <div class="ops-table-wrap">
                        <table class="ops-table">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="ops-num">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data['lowStock'] as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="ops-num">
                                            @if ((int) $row->stock === 0)
                                                <span class="ops-badge ops-badge--danger">0</span>
                                            @else
                                                {{ $fmtQty($row->stock) }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="ops-empty">Belum ada data stok.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>

        <section class="ops-panel ops-chart-panel">
            <div class="ops-panel__head">
                <div>
                    <h3>Tren Penjualan vs Pembelian</h3>
                    <p class="ops-panel__sub">30 hari terakhir</p>
                </div>
            </div>
            <div class="ops-chart-frame">
                <div class="ops-trend" role="img" aria-label="Tren penjualan dan pembelian 30 hari terakhir">
                    @foreach ($trend['labels'] as $index => $label)
                        @php
                            $saleHeight = ((float) ($trend['sales'][$index] ?? 0) / $trendMax) * 100;
                            $buyHeight = ((float) ($trend['purchases'][$index] ?? 0) / $trendMax) * 100;
                        @endphp
                        <div
                            class="ops-trend__day"
                            title="{{ $label }} - Penjualan {{ $fmtRp($trend['sales'][$index] ?? 0) }}, Pembelian {{ $fmtRp($trend['purchases'][$index] ?? 0) }}"
                            style="--sale-height: {{ $saleHeight }}; --buy-height: {{ $buyHeight }};"
                        >
                            <span class="ops-trend__bar ops-trend__bar--sale"></span>
                            <span class="ops-trend__bar ops-trend__bar--buy"></span>
                            @if ($loop->first || $loop->iteration % 5 === 0 || $loop->last)
                                <span class="ops-trend__label">{{ $label }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="ops-chart-legend">
                <div class="ops-legend__item"><span class="ops-dot ops-dot--sale"></span><span>Penjualan</span></div>
                <div class="ops-legend__item"><span class="ops-dot ops-dot--buy"></span><span>Pembelian</span></div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
