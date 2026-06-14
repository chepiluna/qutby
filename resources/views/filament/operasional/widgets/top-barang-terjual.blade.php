@php
    $circumference = 263.894;
    $tunaiDash = ($tunaiPercentage / 100) * $circumference;
    $kreditDash = ($kreditPercentage / 100) * $circumference;
    $kreditOffset = -$tunaiDash;
@endphp

<x-filament-widgets::widget>
    <div class="sales-dashboard-split" style="display: grid; grid-template-columns: minmax(360px, 1fr) 260px 260px; gap: 14px; align-items: stretch;">
        <section class="sales-dashboard-card sales-dashboard-card--table" style="min-height: 205px; border: 1px solid #d5d9df; border-radius: 8px; background: #fff; padding: 18px;">
            <div class="sales-dashboard-card__header" style="margin-bottom: 10px;">
                <h2 style="margin: 0; color: #111827; font-size: 14px; font-weight: 800; line-height: 1.2;">Top Barang Terjual</h2>
            </div>

            <div class="sales-top-table-wrap" style="overflow-x: auto;">
                <table class="sales-top-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="border-bottom: 1px solid #e5c0c0; color: #b91c1c; font-weight: 800; padding: 9px 6px; text-align: left;">Barang</th>
                            <th class="sales-top-table__number" style="border-bottom: 1px solid #e5c0c0; color: #b91c1c; font-weight: 800; padding: 9px 6px; text-align: right;">Qty</th>
                            <th class="sales-top-table__number" style="border-bottom: 1px solid #e5c0c0; color: #b91c1c; font-weight: 800; padding: 9px 6px; text-align: right;">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topBarang as $barang)
                            <tr>
                                <td style="border-bottom: 1px solid #e5e7eb; color: #111827; padding: 10px 6px; white-space: nowrap;">{{ $barang->nama_barang }}</td>
                                <td class="sales-top-table__number" style="border-bottom: 1px solid #e5e7eb; color: #111827; padding: 10px 6px; text-align: right; white-space: nowrap;">{{ number_format((float) $barang->qty, 0, ',', '.') }}</td>
                                <td class="sales-top-table__number" style="border-bottom: 1px solid #e5e7eb; color: #111827; padding: 10px 6px; text-align: right; white-space: nowrap;">Rp {{ number_format((float) $barang->omzet, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="sales-top-table__empty" style="color: #64748b; padding: 14px 6px; text-align: center;">Belum ada data penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="sales-dashboard-card sales-dashboard-card--donut" style="min-height: 205px; border: 1px solid #d5d9df; border-radius: 8px; background: #fff; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div class="sales-dashboard-card__header" style="margin-bottom: 8px;">
                <h2 style="margin: 0; color: #111827; font-size: 14px; font-weight: 800; line-height: 1.2;">Tunai vs Kredit</h2>
            </div>

            <div class="sales-donut" style="position: relative; width: 138px; height: 138px; margin: 0 auto 14px;">
                <svg viewBox="0 0 120 120" width="138" height="138" role="img" aria-label="Tunai {{ $tunaiPercentage }} persen, kredit {{ $kreditPercentage }} persen" style="display: block; transform: rotate(-90deg);">
                    <circle cx="60" cy="60" r="42" fill="none" stroke="#eef2f7" stroke-width="16"></circle>
                    <circle cx="60" cy="60" r="42" fill="none" stroke="#BFE2FF" stroke-width="16" stroke-dasharray="{{ $tunaiDash }} {{ $circumference }}"></circle>
                    <circle cx="60" cy="60" r="42" fill="none" stroke="#1F6EAD" stroke-width="16" stroke-dasharray="{{ $kreditDash }} {{ $circumference }}" stroke-dashoffset="{{ $kreditOffset }}"></circle>
                </svg>

                <div class="sales-donut__label" style="position: absolute; inset: 50% auto auto 50%; transform: translate(-50%, -50%); width: 56px; height: 56px; border-radius: 999px; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #1f6ead; line-height: 1;">
                    <strong style="font-size: 14px; font-weight: 900;">{{ $kreditPercentage }}%</strong>
                    <span style="font-size: 10px; font-weight: 700;">Kredit</span>
                </div>
            </div>

            <div class="sales-donut-legend" style="display: grid; gap: 6px; font-size: 11.5px;">
                <div class="sales-donut-legend__item" style="display: grid; grid-template-columns: auto auto 1fr; align-items: center; gap: 5px; color: #111827; white-space: nowrap;">
                    <span class="sales-donut-legend__dot sales-donut-legend__dot--credit" style="width: 7px; height: 7px; border-radius: 999px; background: #1F6EAD;"></span>
                    <span>Kredit</span>
                    <strong style="font-weight: 800; text-align: right;">Rp {{ number_format($kredit, 0, ',', '.') }}</strong>
                </div>
                <div class="sales-donut-legend__item" style="display: grid; grid-template-columns: auto auto 1fr; align-items: center; gap: 5px; color: #111827; white-space: nowrap;">
                    <span class="sales-donut-legend__dot sales-donut-legend__dot--cash" style="width: 7px; height: 7px; border-radius: 999px; background: #BFE2FF;"></span>
                    <span>Tunai</span>
                    <strong style="font-weight: 800; text-align: right;">Rp {{ number_format($tunai, 0, ',', '.') }}</strong>
                </div>
            </div>
        </section>

        <section class="sales-dashboard-card sales-dashboard-card--stock" style="min-height: 205px; border: 1px solid #d5d9df; border-radius: 8px; background: #fff; padding: 18px;">
            <div class="sales-dashboard-card__header" style="margin-bottom: 10px;">
                <h2 style="margin: 0; color: #111827; font-size: 14px; font-weight: 800; line-height: 1.2;">Stok Barang Menipis</h2>
            </div>

            <div class="sales-top-table-wrap" style="overflow-x: auto;">
                <table class="sales-top-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr>
                            <th style="border-bottom: 1px solid #e5c0c0; color: #b91c1c; font-weight: 800; padding: 9px 6px; text-align: left;">Barang</th>
                            <th class="sales-top-table__number" style="border-bottom: 1px solid #e5c0c0; color: #b91c1c; font-weight: 800; padding: 9px 6px; text-align: right;">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stokMenipis as $barang)
                            <tr>
                                <td style="border-bottom: 1px solid #e5e7eb; color: #111827; padding: 10px 6px; white-space: nowrap;">{{ $barang->nama_barang }}</td>
                                <td class="sales-top-table__number" style="border-bottom: 1px solid #e5e7eb; color: #111827; padding: 10px 6px; text-align: right; white-space: nowrap;">{{ number_format((int) $barang->stok, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="sales-top-table__empty" style="color: #64748b; padding: 14px 6px; text-align: center;">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-widgets::widget>
