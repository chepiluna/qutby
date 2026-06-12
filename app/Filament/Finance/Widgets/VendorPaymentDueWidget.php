<?php

namespace App\Filament\Finance\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VendorPaymentDueWidget extends Widget
{
    private const DISMISSED_SESSION_KEY = 'finance_vendor_payment_due_dismissed_ids';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.finance.widgets.vendor-payment-due';

    protected function getViewData(): array
    {
        $rows = $this->getVendorPaymentDueRows();

        return [
            'rows' => $rows,
            'newCount' => $rows->count(),
            'todayLabel' => $this->formatIndonesianDate(now()),
        ];
    }

    public function dismissVendorPaymentDueNotification(int $terminId): void
    {
        $this->dismissVendorPaymentDueNotifications([$terminId]);
    }

    public function dismissVisibleVendorPaymentDueNotifications(): void
    {
        $terminIds = $this->getVendorPaymentDueRows()
            ->pluck('id')
            ->all();

        $this->dismissVendorPaymentDueNotifications($terminIds);
    }

    protected function getVendorPaymentDueRows(): Collection
    {
        $today = now()->startOfDay();
        $dismissedIds = session(self::DISMISSED_SESSION_KEY, []);

        return DB::table('po_termins')
            ->join('pembelians', 'pembelians.id', '=', 'po_termins.pembelian_id')
            ->leftJoin('vendors', 'vendors.id', '=', 'pembelians.vendor_id')
            ->select([
                'po_termins.id',
                'po_termins.due_date',
                'po_termins.nominal',
                'po_termins.status',
                'pembelians.nomor',
                DB::raw('COALESCE(vendors.nama_vendor, "-") as vendor_name'),
            ])
            ->where(function ($query): void {
                $query
                    ->whereNull('po_termins.status')
                    ->orWhere('po_termins.status', '!=', 'lunas');
            })
            ->whereDate('po_termins.due_date', '>=', $today->toDateString())
            ->whereDate('po_termins.due_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->orderByRaw("
                CASE
                    WHEN po_termins.due_date = ? THEN 0
                    ELSE 1
                END
            ", [$today->toDateString()])
            ->orderBy('po_termins.due_date')
            ->limit(25)
            ->get()
            ->map(function ($row) use ($today): array {
                $dueDate = Carbon::parse($row->due_date)->startOfDay();
                $days = (int) $today->diffInDays($dueDate, false);
                $isPaid = $row->status === 'lunas';

                if ($isPaid) {
                    $urgency = 'paid';
                    $badge = 'Lunas';
                } elseif ($days < 0) {
                    $urgency = 'overdue';
                    $badge = 'Lewat ' . abs($days) . 'h';
                } elseif ($days <= 7) {
                    $urgency = 'soon';
                    $badge = $days . ' hari lagi';
                } else {
                    $urgency = 'safe';
                    $badge = $days . ' hari lagi';
                }

                return [
                    'id' => (int) $row->id,
                    'vendor' => $row->vendor_name,
                    'number' => $row->nomor ?: '-',
                    'dueDate' => $dueDate,
                    'amount' => (float) $row->nominal,
                    'isPaid' => $isPaid,
                    'urgency' => $urgency,
                    'badge' => $badge,
                ];
            })
            ->reject(fn (array $row): bool => in_array($row['id'], $dismissedIds, true))
            ->take(8)
            ->values();
    }

    protected function dismissVendorPaymentDueNotifications(array $terminIds): void
    {
        if ($terminIds === []) {
            return;
        }

        $dismissedIds = session(self::DISMISSED_SESSION_KEY, []);

        session([
            self::DISMISSED_SESSION_KEY => array_values(array_unique([
                ...$dismissedIds,
                ...array_map('intval', $terminIds),
            ])),
        ]);
    }

    protected function formatIndonesianDate(Carbon $date): string
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        return $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }
}
