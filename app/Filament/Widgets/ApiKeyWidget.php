<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ApiKeyWidget extends Widget
{
    protected string $view = 'filament.widgets.api-key-widget';

    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 1;

    public bool $revealed = false;

    public ?string $customToken = null;

    public bool $editMode = false;

    public function mount(): void
    {
        $user = auth()->user();

        // Auto-generate token on first visit if none exists
        if (! $user->api_token) {
            $this->generateToken();
        }
    }

    public function getToken(): ?string
    {
        return auth()->user()?->api_token;
    }

    public function getMaskedToken(): string
    {
        $token = $this->getToken();
        if (! $token) return '—';
        return substr($token, 0, 8) . str_repeat('•', 20);
    }

    public function toggleReveal(): void
    {
        $this->revealed = ! $this->revealed;
    }

    public function generateToken(): void
    {
        $user = auth()->user();

        // Delete old "api-key" named Sanctum tokens
        $user->tokens()->where('name', 'api-key')->delete();

        // Create new Sanctum token and store plain text
        $plain = $user->createToken('api-key')->plainTextToken;
        $user->update(['api_token' => $plain]);

        $this->revealed = true;
        $this->editMode = false;

        Notification::make()
            ->title('API Key berhasil diperbarui')
            ->body('Token baru telah dibuat. Perbarui token di /docs.')
            ->success()
            ->send();
    }

    public function startEdit(): void
    {
        $this->customToken = '';
        $this->editMode = true;
    }

    public function cancelEdit(): void
    {
        $this->editMode = false;
        $this->customToken = null;
    }

    public function saveCustomToken(): void
    {
        $this->validate([
            'customToken' => 'required|min:16|max:255',
        ]);

        $user = auth()->user();
        $user->update(['api_token' => $this->customToken]);

        $this->editMode = false;
        $this->revealed = true;
        $this->customToken = null;

        Notification::make()
            ->title('API Key diperbarui')
            ->success()
            ->send();
    }

    public function openDocs(): void
    {
        $this->redirect(route('docs.api'));
    }
}
