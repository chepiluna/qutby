<x-filament-panels::page>
    @php
        $cards = $this->getKartuPerpetualData();
        $fmtQty = fn ($value) => (float) $value > 0 ? number_format((float) $value, 0, ',', '.') : '-';
        $fmtRp = fn ($value) => (float) $value > 0 ? number_format((float) $value, 0, ',', '.') : '-';
    @endphp

    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
            <div style="display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap;">
                <div style="width: 200px;">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Bulan</label>
                    <select wire:model.live="bulan" style="width:100%; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111827; font-size:14px; padding:7px 10px; cursor:pointer; height:38px;">
                        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $v => $l)
                            <option value="{{ $v }}" @selected($bulan === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="width: 160px;">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Tahun</label>
                    <select wire:model.live="tahun" style="width:100%; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111827; font-size:14px; padding:7px 10px; cursor:pointer; height:38px;">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected((int) $tahun === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="width: 280px;">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Barang</label>
                    <select wire:model.live="barangId" style="width:100%; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#111827; font-size:14px; padding:7px 10px; cursor:pointer; height:38px;">
                        <option value="">Pilih Barang</option>
                        @foreach($this->getBarangOptions() as $id => $namaBarang)
                            <option value="{{ $id }}">{{ $namaBarang }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('kartu-stok.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; background-color:#3b82f6; color:#ffffff; padding:0 16px; border-radius:6px; font-size:14px; font-weight:500; text-decoration:none; height:38px; border:1px solid #2563eb;">
                    Cetak PDF
                </a>
            </div>
        </div>

        <div style="padding:20px;">
            <div style="margin-bottom:18px;">
                <h2 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 4px;">
                    Kartu Stok FIFO Perpetual - Periode: {{ $this->getPeriodeLabel() }}
                </h2>
                <p style="font-size:12px; color:#6b7280; margin:0;">Setiap barang ditampilkan dalam kartu terpisah. Persediaan menunjukkan layer FIFO aktif setelah transaksi.</p>
            </div>

            @if($barangId === '')
                <div style="text-align:center; padding:60px 20px; color:#6b7280; font-size:14px; border:1px dashed #d1d5db; border-radius:8px; background:#f9fafb;">
                    Pilih barang terlebih dahulu untuk menampilkan kartu stok.
                </div>
            @elseif(empty($cards))
                <div style="text-align:center; padding:60px 0; color:#9ca3af; font-size:14px;">
                    Tidak ada saldo atau mutasi untuk barang ini di periode yang dipilih.
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:28px;">
                    @foreach($cards as $card)
                        <section style="border:1px solid #d1d5db; border-radius:8px; overflow:hidden; background:#ffffff;">
                            <div style="padding:14px 16px; background:#ffffff; border-bottom:1px solid #d1d5db;">
                                <h3 style="margin:0; color:#111827; font-size:15px; font-weight:800; text-transform:uppercase;">
                                    Kartu Stok {{ $card['barang']->nama_barang }}
                                </h3>
                                <div style="margin-top:4px; color:#6b7280; font-size:12px;">
                                    Kode: {{ $card['barang']->kode_barang ?? '-' }}
                                </div>
                            </div>

                            <div style="overflow-x:auto;">
                                <table style="width:100%; min-width:1120px; border-collapse:collapse; font-size:12px; color:#111827;">
                                    <thead>
                                        <tr style="background:#f3f4f6;">
                                            <th rowspan="2" style="border:1px solid #d1d5db; padding:8px; text-align:center; font-weight:700;">Tanggal</th>
                                            <th rowspan="2" style="border:1px solid #d1d5db; padding:8px; text-align:left; font-weight:700;">Keterangan</th>
                                            <th colspan="3" style="border:1px solid #d1d5db; padding:8px; text-align:center; font-weight:700;">Pembelian</th>
                                            <th colspan="3" style="border:1px solid #d1d5db; padding:8px; text-align:center; font-weight:700;">HPP</th>
                                            <th colspan="3" style="border:1px solid #d1d5db; padding:8px; text-align:center; font-weight:700;">Persediaan</th>
                                        </tr>
                                        <tr style="background:#f9fafb;">
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:center; font-weight:700;">Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Harga/Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Total</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:center; font-weight:700;">Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Harga/Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Total</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:center; font-weight:700;">Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Harga/Unit</th>
                                            <th style="border:1px solid #d1d5db; padding:7px; text-align:right; font-weight:700;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($card['rows'] as $row)
                                            @php
                                                $hppRows = $row['hpp_rows'] ?: [['qty' => 0, 'harga' => 0, 'total' => 0]];
                                                $persediaanRows = $row['persediaan_rows'] ?: [['qty' => 0, 'harga' => 0, 'total' => 0]];
                                                $lineCount = max(count($hppRows), count($persediaanRows), 1);
                                            @endphp

                                            @for($i = 0; $i < $lineCount; $i++)
                                                @php
                                                    $hpp = $hppRows[$i] ?? ['qty' => 0, 'harga' => 0, 'total' => 0];
                                                    $persediaan = $persediaanRows[$i] ?? ['qty' => 0, 'harga' => 0, 'total' => 0];
                                                @endphp
                                                <tr>
                                                    @if($i === 0)
                                                        <td rowspan="{{ $lineCount }}" style="border:1px solid #d1d5db; padding:8px; text-align:center; vertical-align:top; white-space:nowrap;">{{ $row['tanggal'] }}</td>
                                                        <td rowspan="{{ $lineCount }}" style="border:1px solid #d1d5db; padding:8px; vertical-align:top; min-width:180px;">{{ $row['keterangan'] }}</td>
                                                        <td rowspan="{{ $lineCount }}" style="border:1px solid #d1d5db; padding:8px; text-align:center; vertical-align:top;">{{ $fmtQty($row['pembelian']['qty'] ?? 0) }}</td>
                                                        <td rowspan="{{ $lineCount }}" style="border:1px solid #d1d5db; padding:8px; text-align:right; vertical-align:top;">{{ $fmtRp($row['pembelian']['harga'] ?? 0) }}</td>
                                                        <td rowspan="{{ $lineCount }}" style="border:1px solid #d1d5db; padding:8px; text-align:right; vertical-align:top;">{{ $fmtRp($row['pembelian']['total'] ?? 0) }}</td>
                                                    @endif

                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:center;">{{ $fmtQty($hpp['qty']) }}</td>
                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:right;">{{ $fmtRp($hpp['harga']) }}</td>
                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:right;">{{ $fmtRp($hpp['total']) }}</td>
                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:center;">{{ $fmtQty($persediaan['qty']) }}</td>
                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:right;">{{ $fmtRp($persediaan['harga']) }}</td>
                                                    <td style="border:1px solid #d1d5db; padding:8px; text-align:right;">{{ $fmtRp($persediaan['total']) }}</td>
                                                </tr>
                                            @endfor
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f9fafb; font-weight:700;">
                                            <td colspan="2" style="border:1px solid #d1d5db; padding:9px; text-align:center;">Total</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:center;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">{{ $fmtRp($card['total_pembelian']) }}</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:center;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">{{ $fmtRp($card['total_hpp']) }}</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:center;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">-</td>
                                            <td style="border:1px solid #d1d5db; padding:9px; text-align:right;">{{ $fmtRp($card['persediaan_akhir']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div style="padding:12px 16px; display:flex; gap:16px; flex-wrap:wrap; align-items:center; border-top:1px solid #e5e7eb; background:#f9fafb; font-size:12px;">
                                <span>Saldo Awal: <strong>Rp {{ $fmtRp($card['saldo_awal_nilai']) }}</strong></span>
                                <span>Total Pembelian: <strong>Rp {{ $fmtRp($card['total_pembelian']) }}</strong></span>
                                <span>Total HPP: <strong>Rp {{ $fmtRp($card['total_hpp']) }}</strong></span>
                                <span>Persediaan Akhir: <strong>Rp {{ $fmtRp($card['persediaan_akhir']) }}</strong></span>
                                <span style="color:#111827; font-weight:700;">
                                    Validasi: {{ $card['valid'] ? 'Sesuai' : 'Tidak sesuai' }}
                                </span>
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
