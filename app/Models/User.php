<?php

namespace App\Models;

use App\Support\StaffAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','is_admin','role','is_active','email_verified_at',
        'last_login_at','last_login_ip_hash','failed_login_count','locked_until',
        'password_changed_at','force_password_reset','two_factor_secret','two_factor_enabled_at','two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password','remember_token','two_factor_secret','two_factor_recovery_codes','last_login_ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'force_password_reset' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_enabled_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'failed_login_count' => 'integer',
        ];
    }

    public function staffRole(): ?string
    {
        if ($this->is_admin !== true) {
            return null;
        }

        $role = trim((string) $this->role);

        // Backward compatibility for the original administrator account.
        // New privileged accounts are always created with an explicit role.
        if ($role === '') {
            return 'super_admin';
        }

        return StaffAccess::isKnownRole($role) ? $role : null;
    }

    public function staffRoleLabel(): string
    {
        return StaffAccess::ROLE_LABELS[$this->staffRole()] ?? 'Not authorized';
    }

    public function staffRoleDescription(): string
    {
        return StaffAccess::ROLE_DESCRIPTIONS[$this->staffRole()] ?? 'No privileged access is assigned.';
    }

    public function hasStaffPermission(string $permission): bool
    {
        return $this->is_admin === true
            && $this->is_active !== false
            && StaffAccess::roleHasPermission($this->staffRole(), $permission);
    }

    public function hasAnyStaffPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasStaffPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->staffRole() === 'super_admin';
    }

    public function isPrincipal(): bool
    {
        return $this->staffRole() === 'principal';
    }

    public function isTeacher(): bool
    {
        return $this->staffRole() === 'teacher';
    }

    public function isSpecializedEditor(): bool
    {
        return str_ends_with((string) $this->staffRole(), '_editor');
    }

    public function canManageSchoolSettings(): bool
    {
        return $this->hasStaffPermission('settings.manage');
    }

    public function canManageStaff(): bool
    {
        return $this->hasStaffPermission('staff.manage');
    }

    public function canPostDailyContent(): bool
    {
        return $this->hasAnyStaffPermission([
            'news.manage',
            'events.manage',
            'media.manage',
        ]);
    }

    public function requiresTwoFactorRecommendation(): bool
    {
        return $this->is_admin === true && $this->is_active !== false;
    }

    public function twoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && filled($this->two_factor_enabled_at);
    }

    public function isTemporarilyLocked(): bool
    {
        return $this->locked_until?->isFuture() ?? false;
    }
}
