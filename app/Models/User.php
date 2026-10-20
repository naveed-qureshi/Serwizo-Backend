<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'visible_password',
        'role',
        'brand_id',
        'phone',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'visible_password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getVisiblePasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setVisiblePasswordAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['visible_password'] = null;
        } else {
            $this->attributes['visible_password'] = \Illuminate\Support\Facades\Crypt::encryptString($value);
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isOperations(): bool
    {
        return in_array($this->role, ['super_admin', 'operations']);
    }

    public function isAccounts(): bool
    {
        return in_array($this->role, ['super_admin', 'accounts']);
    }

    public function isBrand(): bool
    {
        return $this->role === 'brand';
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
