<x-filament-widgets::widget>
    <style>
        .vendor-due-notifications {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.10);
            color: #0f172a;
            overflow: hidden;
        }

        .vendor-due-notifications__header {
            align-items: center;
            background: #b91c1c;
            color: #ffffff;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 14px 22px;
        }

        .vendor-due-notifications__brand {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }

        .vendor-due-notifications__icon {
            align-items: center;
            display: inline-flex;
            flex: 0 0 auto;
            height: 24px;
            justify-content: center;
            width: 24px;
        }

        .vendor-due-notifications__icon svg {
            height: 20px;
            width: 20px;
        }

        .vendor-due-notifications__title {
            font-size: 18px;
            font-weight: 900;
            line-height: 1.1;
            margin: 0;
        }

        .vendor-due-notifications__subtitle {
            color: rgba(255, 255, 255, 0.86);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 3px;
        }

        .vendor-due-notifications__actions {
            align-items: center;
            display: flex;
            flex: 0 0 auto;
            gap: 10px;
        }

        .vendor-due-count,
        .vendor-due-clear {
            align-items: center;
            border-radius: 9px;
            color: #ffffff;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 8px;
            min-height: 32px;
            padding: 0 13px;
            white-space: nowrap;
        }

        .vendor-due-count {
            background: #991b1b;
            border: 1px solid rgba(153, 27, 27, 0.85);
        }

        .vendor-due-clear {
            background: transparent;
            border: 1px solid #7f1d1d;
            color: #0f172a;
            cursor: pointer;
            justify-content: center;
            min-width: 116px;
            transition: background .15s ease, border-color .15s ease;
        }

        .vendor-due-clear:hover {
            background: rgba(127, 29, 29, 0.12);
            border-color: #450a0a;
        }

        .vendor-due-clear:disabled {
            cursor: not-allowed;
            opacity: .70;
        }

        .vendor-due-clear svg {
            height: 16px;
            width: 16px;
        }

        .vendor-due-list {
            background: #ffffff;
        }

        .vendor-due-row {
            align-items: center;
            border-top: 1px solid #edf0f5;
            display: grid;
            gap: 14px;
            grid-template-columns: auto minmax(0, 1fr);
            min-height: 72px;
            padding: 14px 22px;
        }

        .vendor-due-row:first-child {
            border-top: 0;
        }

        .vendor-due-row--clickable {
            cursor: pointer;
            transition: background .15s ease;
        }

        .vendor-due-row--clickable:hover {
            background: #f8fbff;
        }

        .vendor-due-status {
            align-items: center;
            border-radius: 10px;
            display: inline-flex;
            height: 36px;
            justify-content: center;
            width: 36px;
        }

        .vendor-due-status svg {
            height: 18px;
            width: 18px;
        }

        .vendor-due-status--overdue,
        .vendor-due-status--soon {
            background: #fee2e2;
            color: #ef4444;
        }

        .vendor-due-status--safe {
            background: #dcfce7;
            color: #16a34a;
        }

        .vendor-due-status--paid {
            background: #dbeafe;
            color: #2563eb;
        }

        .vendor-due-content {
            min-width: 0;
        }

        .vendor-due-name {
            color: #0f172a;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vendor-due-meta {
            color: #334155;
            font-size: 14px;
            line-height: 1.4;
            margin-top: 5px;
        }

        .vendor-due-empty {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            padding: 18px 22px;
            text-align: center;
        }

        .dark .vendor-due-notifications,
        .dark .vendor-due-list {
            background: #111827;
            border-color: #334155;
            color: #f8fafc;
        }

        .dark .vendor-due-row {
            border-top-color: #334155;
        }

        .dark .vendor-due-row--clickable:hover {
            background: #172033;
        }

        .dark .vendor-due-name {
            color: #f8fafc;
        }

        .dark .vendor-due-meta {
            color: #cbd5e1;
        }

        @media (max-width: 760px) {
            .vendor-due-notifications__header {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px 18px;
            }

            .vendor-due-notifications__actions {
                width: 100%;
            }

            .vendor-due-clear {
                flex: 1;
            }

            .vendor-due-row {
                align-items: start;
                gap: 14px;
                grid-template-columns: auto minmax(0, 1fr);
                padding: 14px 18px;
            }

            .vendor-due-name {
                white-space: normal;
            }
        }
    </style>

    @if ($rows->isNotEmpty())
    <div class="vendor-due-notifications">
        <div class="vendor-due-notifications__header">
            <div class="vendor-due-notifications__brand">
                <span class="vendor-due-notifications__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.4"></circle>
                        <path d="M12 7v6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
                        <path d="M12 16.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="vendor-due-notifications__title">Perlu Perhatian</h3>
                    <div class="vendor-due-notifications__subtitle">Daftar tagihan yang perlu segera dibayar</div>
                </div>
            </div>

            <div class="vendor-due-notifications__actions">
                <span class="vendor-due-count">{{ $newCount }} Baru</span>
                <button
                    type="button"
                    class="vendor-due-clear"
                    wire:click="dismissVisibleVendorPaymentDueNotifications"
                    wire:loading.attr="disabled"
                    wire:target="dismissVisibleVendorPaymentDueNotifications"
                    @disabled($newCount === 0)
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Clear All
                </button>
            </div>
        </div>

        <div class="vendor-due-list">
            @forelse ($rows as $row)
                <div
                    class="vendor-due-row @if (! $row['isPaid']) vendor-due-row--clickable @endif"
                    wire:key="finance-vendor-due-{{ $row['id'] }}"
                    @if (! $row['isPaid'])
                        wire:click="dismissVendorPaymentDueNotification({{ $row['id'] }})"
                        wire:loading.class.remove="vendor-due-row--clickable"
                        wire:target="dismissVendorPaymentDueNotification({{ $row['id'] }})"
                    @endif
                >
                    <span class="vendor-due-status vendor-due-status--{{ $row['urgency'] }}" aria-hidden="true">
                        @if ($row['isPaid'])
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
                            </svg>
                        @endif
                    </span>

                    <div class="vendor-due-content">
                        <div class="vendor-due-name">{{ $row['vendor'] }}</div>
                        <div class="vendor-due-meta">
                            {{ $row['number'] }} tanggal pembayaran {{ $row['dueDate']->format('d/m/Y') }}.
                        </div>
                    </div>
                </div>
            @empty
                <div class="vendor-due-empty">Belum ada jadwal pembayaran vendor.</div>
            @endforelse
        </div>
    </div>
    @endif
</x-filament-widgets::widget>
