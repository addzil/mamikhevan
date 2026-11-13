<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'notification_email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    public const ROLE_ADMINISTRATOR = 'administrator';

    public const ROLE_MANAGEMENT = 'management';

    public const ROLE_OPERATOR = 'operator';

    public const ROLE_CUSTOMER = 'customer';

    public const ADMIN_ROLES = [
        self::ROLE_ADMINISTRATOR,
        self::ROLE_MANAGEMENT,
        self::ROLE_OPERATOR,
    ];

    public const ROLE_LABELS = [
        self::ROLE_ADMINISTRATOR => 'Administrator',
        self::ROLE_MANAGEMENT => 'Manajemen',
        self::ROLE_OPERATOR => 'Operator',
        self::ROLE_CUSTOMER => 'Pelanggan',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if (blank($user->role)) {
                $user->role = self::ROLE_CUSTOMER;
            }

            $user->is_admin = in_array($user->role, self::ADMIN_ROLES, true);
        });
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, self::ADMIN_ROLES, true);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? self::ROLE_LABELS[self::ROLE_CUSTOMER];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
            'is_admin' => 'boolean',
        ];
    }
}
