<?php

namespace App\Filament\Pages;

use App\Models\QrisMarkupSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageQrisMarkup extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manage-qris-markup';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'Markup QRIS';
    }

    public function getTitle(): string
    {
        return 'Pengaturan Markup QRIS';
    }

    public function getSubheading(): ?string
    {
        return 'Atur markup otomatis untuk pembayaran via QRIS. Admin & Reseller tidak terkena markup.';
    }

    public function mount(): void
    {
        $setting = QrisMarkupSetting::current();

        $this->form->fill([
            'markup_deposit_type' => $setting->markup_deposit_type,
            'markup_deposit_value' => $setting->markup_deposit_value,
            'markup_transaction_type' => $setting->markup_transaction_type,
            'markup_transaction_value' => $setting->markup_transaction_value,
            'is_active' => $setting->is_active,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Toggle::make('is_active')
                    ->label('Markup Aktif')
                    ->helperText('Aktifkan/nonaktifkan markup QRIS untuk semua user biasa'),

                Select::make('markup_deposit_type')
                    ->label('Tipe Markup Deposit')
                    ->options([
                        'fixed' => 'Nominal Tetap (Rp)',
                        'percentage' => 'Persentase (%)',
                    ])
                    ->required(),

                TextInput::make('markup_deposit_value')
                    ->label('Nilai Markup Deposit')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('Contoh: 500 (Rp500) atau 0.7 (0.7%)'),

                Select::make('markup_transaction_type')
                    ->label('Tipe Markup Transaksi')
                    ->options([
                        'fixed' => 'Nominal Tetap (Rp)',
                        'percentage' => 'Persentase (%)',
                    ])
                    ->required(),

                TextInput::make('markup_transaction_value')
                    ->label('Nilai Markup Transaksi')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('Contoh: 500 (Rp500) atau 0.7 (0.7%)'),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = QrisMarkupSetting::current();
        $setting->update($data);

        Notification::make()
            ->title('Markup QRIS berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }
}
