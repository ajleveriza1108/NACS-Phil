<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
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
}