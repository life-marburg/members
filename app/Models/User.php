<?php

namespace App\Models;

use App\Rights;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements FilamentUser, HasLocalePreference
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public const STATUS_NEW = 0;

    public const STATUS_UNLOCKED = 1;

    public const STATUS_LOCKED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_active_at',
        'status',
        'disable_after_days',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            $user->personalData()->create();
        });
    }

    public function personalData(): HasOne
    {
        return $this->hasOne(PersonalData::class);
    }

    public function instrumentGroups(): BelongsToMany
    {
        return $this->belongsToMany(InstrumentGroup::class, 'user_instrument_group');
    }

    public function preferredLocale(): string
    {
        return 'de';
    }

    public function hasPersonalData(): bool
    {
        return $this->personalData->street !== null &&
            $this->personalData->zip !== null &&
            $this->personalData->city !== null &&
            $this->personalData->mobile_phone !== null;
    }

    public function additionalEmails(): HasMany
    {
        return $this->hasMany(AdditionalEmails::class);
    }

    protected function allEmails(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', [$this->email, ...$this->additionalEmails->map(fn ($e) => $e->email)])
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->personalData->street.', '.$this->personalData->zip.' '.$this->personalData->city
        );
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasRole(Rights::R_ADMIN),
        );
    }

    protected function canViewAllSheets(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->can(Rights::P_VIEW_ALL_INSTRUMENTS),
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->is_admin ||
                   $this->hasPermissionTo(Rights::P_MANAGE_MEMBERS);
        }

        return false;
    }
}
