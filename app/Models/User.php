<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use juniyasyos\ShieldLite\Concerns\HasShieldRoles;
use juniyasyos\ShieldLite\Concerns\HasShieldPermissions;
use juniyasyos\ShieldLite\Concerns\AuthorizesShield;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasShieldRoles, HasShieldPermissions, AuthorizesShield;
    use HasRoles {
        // Use Spatie methods as primary
        HasRoles::roles insteadof HasShieldRoles;
        HasRoles::assignRole insteadof HasShieldRoles;
        HasRoles::removeRole insteadof HasShieldRoles;
        HasRoles::hasRole insteadof HasShieldRoles;
        HasRoles::hasAnyRole insteadof HasShieldRoles;
        HasRoles::hasAllRoles insteadof HasShieldRoles;
        HasRoles::syncRoles insteadof HasShieldRoles;
        HasRoles::getRoleNames insteadof HasShieldRoles;

        // Keep Shield Lite specific methods with aliases
        HasShieldRoles::isSuperAdmin as isShieldSuperAdmin;
        HasShieldRoles::getDefaultRole as getShieldDefaultRole;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }
}
