<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'title',
        'code',
        'description',
        'estimated_duration_minutes',
        'default_customer_price',
        'default_worker_payment',
        'default_commission',
        'is_promotional',
        'promotional_customer_price',
        'promotional_worker_payment',
        'promotional_commission',
        'status',
        'is_brand_job',
        'brand_id',
        'is_free_installation',
        'brand_rate',
    ];

    protected function casts(): array
    {
        return [
            'default_customer_price' => 'decimal:2',
            'default_worker_payment' => 'decimal:2',
            'default_commission' => 'decimal:2',
            'is_promotional' => 'boolean',
            'promotional_customer_price' => 'decimal:2',
            'promotional_worker_payment' => 'decimal:2',
            'promotional_commission' => 'decimal:2',
            'brand_rate' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'is_brand_job' => 'boolean',
            'is_free_installation' => 'boolean',
        ];
    }

    public function getEffectiveCustomerPriceAttribute(): float
    {
        if ($this->is_promotional && $this->promotional_customer_price !== null) {
            return (float)$this->promotional_customer_price;
        }
        return (float)$this->default_customer_price;
    }

    public function getEffectiveWorkerPaymentAttribute(): float
    {
        if ($this->is_promotional && $this->promotional_worker_payment !== null) {
            return (float)$this->promotional_worker_payment;
        }
        return (float)$this->default_worker_payment;
    }

    public function getEffectiveCommissionAttribute(): float
    {
        if ($this->is_promotional && $this->promotional_commission !== null) {
            return (float)$this->promotional_commission;
        }
        return (float)$this->default_commission;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_promotional || !$this->promotional_customer_price || !$this->default_customer_price) {
            return null;
        }
        if ($this->default_customer_price <= $this->promotional_customer_price) {
            return null;
        }
        return (int)round((($this->default_customer_price - $this->promotional_customer_price) / $this->default_customer_price) * 100);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function prices()
    {
        return $this->hasMany(ServiceJobPrice::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function getPriceForCity(?int $cityId)
    {
        if ($cityId) {
            $cityPrice = $this->prices()->where('city_id', $cityId)->first();
            if ($cityPrice) {
                return [
                    'customer_price' => $cityPrice->customer_price,
                    'worker_payment' => $cityPrice->worker_payment,
                    'commission' => $cityPrice->commission,
                ];
            }
        }

        return [
            'customer_price' => $this->default_customer_price,
            'worker_payment' => $this->default_worker_payment,
            'commission' => $this->default_commission,
        ];
    }
}
