<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Worker extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'worker_code',
        'name',
        'cnic_number',
        'phone',
        'email',
        'date_of_birth',
        'address',
        'primary_city_id',
        'primary_category_id',
        'primary_sub_category_id',
        'experience_years',
        'skill_level',
        'status',
        'availability',
        'password',
        'visible_password',
        'device_token',
        'photo_path',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'visible_password',
    ];

    protected $appends = [
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'experience_years' => 'integer',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
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

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }
        return route('workers.photo', $this->id);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    public function primaryCity()
    {
        return $this->belongsTo(City::class, 'primary_city_id');
    }

    public function workerCategories()
    {
        return $this->hasMany(WorkerCategory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'worker_categories');
    }

    public function serviceAreas()
    {
        return $this->hasMany(WorkerServiceArea::class);
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'worker_service_areas');
    }

    public function bankDetail()
    {
        return $this->hasOne(WorkerBankDetail::class);
    }

    public function references()
    {
        return $this->hasMany(WorkerReference::class);
    }

    public function documents()
    {
        return $this->hasMany(WorkerDocument::class);
    }

    public function assignments()
    {
        return $this->hasMany(WorkerAssignment::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'current_worker_id');
    }

    public function ledgerTransactions()
    {
        return $this->hasMany(LedgerTransaction::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function payments()
    {
        return $this->hasMany(WorkerPayment::class);
    }

    public function notifications()
    {
        return $this->morphMany(AppNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function getLedgerBalance(): float
    {
        $lastTx = $this->ledgerTransactions()->first();
        return $lastTx ? (float)$lastTx->running_balance : 0.00;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->availability === 'available';
    }
}
