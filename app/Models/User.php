<?php

namespace App\Models;

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

        return $this->role ?: 'super_admin';
    }

    public function staffRoleLabel(): string
    {
        return match ($this->staffRole()) {
            'super_admin' => 'Super Admin',
            'principal' => 'Principal / School Admin',
            'teacher' => 'Teacher / Content Editor',
            default => 'Not authorized',
        };
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

    public function canManageSchoolSettings(): bool
    {
        return $this->is_active !== false
            && in_array($this->staffRole(), ['super_admin', 'principal'], true);
    }

    public function canManageStaff(): bool
    {
        return $this->is_active !== false && $this->isSuperAdmin();
    }

    public function canPostDailyContent(): bool
    {
        return $this->is_active !== false
            && in_array($this->staffRole(), ['super_admin', 'principal', 'teacher'], true);
    }

    public function requiresTwoFactorRecommendation(): bool
    {
        return in_array($this->staffRole(), ['super_admin', 'principal'], true);
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
