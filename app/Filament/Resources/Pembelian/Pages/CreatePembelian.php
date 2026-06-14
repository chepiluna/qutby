<?php

namespace App\Filament\Resources\Pembelian\Pages;

//use App\Filament\Traits\HasBackButtonHeading;

use App\Filament\Resources\Pembelian\PembelianResource;
use App\Models\Pembelian;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class CreatePembelian extends CreateRecord
{
    //use HasBackButtonHeading;

    protected static string $resource = PembelianResource::class;

    public function getTitle(): string
    {
        return 'Pesanan Pembelian';
    }

    public function getHeading(): HtmlString
    {
        $url = e($this->getResource()::getUrl('index'));

        return new HtmlString(<<<HTML
            <span style="display:inline-flex; align-items:center; gap:12px;">
                <a href="{$url}" aria-label="Kembali ke daftar pesanan pembelian" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:9999px; background:#000000; color:#ffffff; text-decoration:none; line-height:1;">
                    <span style="font-size:30px; font-weight:800; transform:translate(-1px, -1px);">&lsaquo;</span>
                </a>
                <span>Pesanan Pembelian</span>
            </span>
        HTML);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nomor'] = Pembelian::generateNomorPembelian($data['tanggal'] ?? null);
        $data['status'] = 'menunggu';

        if (($data['syarat_pembayaran'] ?? null) === 'tunai') {
            $data['vendor_id'] = null;

            if (filled($data['vendor_manual'] ?? null)) {
                $data['vendor_manual'] = trim((string) $data['vendor_manual']);
            } else {
                $data['vendor_manual'] = null;
            }
        } else {
            $data['vendor_manual'] = null;
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('create'),

            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
