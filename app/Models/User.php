<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
#[Fillable(['name', 'email', 'password', 'phone', 'balance', 'role', 'is_banned', 'ban_reason', 'api_token'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (User $user) {
            if (empty($user->api_token)) {
                $user->api_token = 'wtu_' . Str::random(40);
            }
        });
    }

    public function generateApiToken(): string
    {
        $token = 'wtu_' . Str::random(40);
        $this->update(['api_token' => $token]);
        return $token;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_banned' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    // ── Role helpers ────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }


    /**
     * Check if user is exempt from product price markup.
     * Admin and Reseller buy at base price (no markup).
     */
    public function isExemptFromMarkup(): bool
    {
        return $this->isAdmin() || $this->isReseller();
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    // ── Relationships ───────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
}
