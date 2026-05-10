<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewOrder extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.orders.pages.view-order';

    public Order $record;

    public function getTitle(): string
    {
        return 'Invoice #' . str_pad($this->record->id, 6, '0', STR_PAD_LEFT);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(OrderResource::getUrl('index')),
        ];
    }

    public function getStatusColor(): string
    {
        return match ($this->record->status) {
            'completed' => 'green',
            'processing' => 'amber',
            'pending' => 'gray',
            'failed' => 'red',
            'refunded' => 'blue',
            default => 'gray',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->record->status) {
            'completed' => 'Selesai',
            'processing' => 'Diproses',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Gagal',
            'refunded' => 'Dikembalikan',
            default => ucfirst($this->record->status),
        };
    }

    public function getPaymentMethodLabel(): string
    {
        return match ($this->record->payment_method) {
            'balance' => 'Saldo',
            'midtrans' => 'Midtrans',
            'qris' => 'QRIS',
            default => $this->record->payment_method,
        };
    }

    public function getPaymentStatusLabel(): string
    {
        return match ($this->record->payment_status) {
            'lunas' => 'Lunas',
            'belum' => 'Belum Lunas',
            default => '-',
        };
    }

    public function getProviderLabel(): string
    {
        return match ($this->record->provider) {
            'okeconnect' => 'OkeConnect',
            'smmpanel' => 'SMM Panel',
            'digital' => 'Produk Digital',
            default => ucfirst($this->record->provider),
        };
    }
}
