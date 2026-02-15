<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Carbon\Carbon;

class CouponCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
        'minimum_amount',
        'applicable_plans',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'applicable_plans' => 'array',
        'metadata' => 'array'
    ];

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';
    const TYPE_FREE_DAYS = 'free_days';

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class, 'coupon_code', 'code');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function canBeUsedBy($user, string $planType = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check if user has already used this coupon
        if ($this->isUsedBy($user)) {
            return false;
        }

        // Check if coupon is applicable to the plan type
        if ($planType && $this->applicable_plans && !in_array($planType, $this->applicable_plans)) {
            return false;
        }

        return true;
    }

    public function isUsedBy($user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function users()
    {
        return $this->belongsToMany(
            config('shopify-enhanced.user_model', \App\Models\User::class),
            'coupon_user_usage',
            'coupon_code_id',
            'user_id'
        );
    }

    public function use(): void
    {
        $this->increment('used_count');
    }

    public function recordUsage($user): void
    {
        // Record that this user has used this coupon
        $this->users()->attach($user->id);
    }

    public function getDiscountAmount(float $baseAmount): float
    {
        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                return $baseAmount * ($this->value / 100);
            case self::TYPE_FIXED:
                return min($this->value, $baseAmount);
            default:
                return 0;
        }
    }

    public function getFreeDays(): int
    {
        return $this->type === self::TYPE_FREE_DAYS ? (int) $this->value : 0;
    }
}