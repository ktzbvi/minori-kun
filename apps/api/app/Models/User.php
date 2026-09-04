<?php

namespace App\Models;

use App\Enums\AccountState;
use App\Enums\ScreeningState;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    protected $guarded = [];

    protected $hidden = ['password'];

    public function buyerProfile(): HasOne
    {
        return $this->hasOne(BuyerProfile::class);
    }

    public function producerProfile(): HasOne
    {
        return $this->hasOne(ProducerProfile::class);
    }

    public function payjpTenant(): HasOne
    {
        return $this->hasOne(PayjpTenant::class, 'producer_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'producer_id');
    }

    public function isRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isSellingEligible(): bool
    {
        if (! $this->isRole(UserRole::Producer)
            || ! $this->producerProfile()->whereNotNull('selling_eligible_at')->exists()) {
            return false;
        }

        return $this->payjpTenant()
            ->whereHas('screenings', fn ($query) => $query->where('card_brand', 'visa')->where('state', ScreeningState::Passed->value))
            ->whereHas('screenings', fn ($query) => $query->where('card_brand', 'mastercard')->where('state', ScreeningState::Passed->value))
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'account_state' => AccountState::class,
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
