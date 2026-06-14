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

    protected static bool $isLazy = false;

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

    public function dismissVendorPaymentDueNotification(int $purchaseId): void
    {
        $this->dismissVendorPaymentDueNotifications([$purchaseId]);
    }

    public function dismissVisibleVendorPaymentDueNotifications(): void
    {
        $purchaseIds = $this->getVendorPaymentDueRows()
            ->pluck('id')
            ->all();

        $this->dismissVendorPaymentDueNotifications($purchaseIds);
    }

    protected function getVendorPaymentDueRows(): Collection
    {
        $today = now()->startOfDay();
        $dismissedIds = session(self::DISMISSED_SESSION_KEY, []);

        return DB::table('pembelians')
            ->leftJoin('vendors', 'vendors.id', '=', 'pembelians.vendor_id')
            ->select([
                'pembelians.id',
                'pembelians.tanggal',
                'pembelians.total_akhir',
                'pembelians.status',
                'pembelians.nomor',
                'vendors.periode_pembayaran',
                DB::raw('COALESCE(vendors.nama_vendor, "-") as vendor_name'),
            ])
            ->where('pembelians.syarat_pembayaran', 'kredit')
            ->where(function ($query): void {
                $query
                    ->whereNull('pembelians.status')
                    ->orWhere('pembelians.status', '!=', 'lunas');
            })
            ->get()
            ->map(function ($row) use ($today): array {
                $dueDate = Carbon::parse($row->tanggal)
                    ->addMonthsNoOverflow((int) ($row->periode_pembayaran ?: 1))
                    ->startOfDay();
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
                    'amount' => (float) $row->total_akhir,
                    'isPaid' => $isPaid,
                    'urgency' => $urgency,
                    'badge' => $badge,
                ];
            })
            ->filter(fn (array $row): bool => $row['dueDate']->gte($today) && $row['dueDate']->lte($today->copy()->addDays(7)))
            ->sortBy(fn (array $row): string => $row['dueDate']->toDateString())
            ->reject(fn (array $row): bool => in_array($row['id'], $dismissedIds, true))
            ->take(8)
            ->values();
    }

    protected function dismissVendorPaymentDueNotifications(array $purchaseIds): void
    {
        if ($purchaseIds === []) {
            return;
        }

        $dismissedIds = session(self::DISMISSED_SESSION_KEY, []);

        session([
            self::DISMISSED_SESSION_KEY => array_values(array_unique([
                ...$dismissedIds,
                ...array_map('intval', $purchaseIds),
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
