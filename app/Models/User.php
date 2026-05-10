<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'role',
        'is_banned',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'integer',
        'is_banned' => 'boolean',
    ];

    /**
     * Filament Access Control
     * Syarat agar user bisa login ke dashboard Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya Admin yang tidak kena ban yang bisa masuk
        return $this->isAdmin() && !$this->isBanned();
    }

    // ── Role Checks ──────────────────────────────────────────────

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
     * Cek apakah user bebas dari markup harga (Admin & Reseller)
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