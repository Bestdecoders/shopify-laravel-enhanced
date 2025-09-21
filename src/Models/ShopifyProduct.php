<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ShopifyProduct extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'shop_domain',
        'title',
        'handle',
        'vendor',
        'product_type',
        'status',
        'collection_ids',
        'queried_collection_ids',
        'tags',
        'last_accessed_at',
        'shopify_updated_at',
        'price',
        'compare_at_price',
        'inventory_quantity',
        'weight',
    ];

    protected $casts = [
        'collection_ids' => 'array',
        'queried_collection_ids' => 'array',
        'tags' => 'array',
        'last_accessed_at' => 'datetime',
        'shopify_updated_at' => 'datetime',
        'price' => 'integer',
        'compare_at_price' => 'integer',
        'inventory_quantity' => 'integer',
        'weight' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('shopify-enhanced.user_model', \App\Models\User::class));
    }

    public function scopeByShop($query, $shopDomain)
    {
        return $query->where('shop_domain', $shopDomain);
    }

    public function scopeByVendor($query, $vendor)
    {
        return $query->where('vendor', $vendor);
    }

    public function scopeByProductType($query, $productType)
    {
        return $query->where('product_type', $productType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInCollection($query, $collectionId)
    {
        return $query->whereJsonContains('collection_ids', $collectionId);
    }

    public function scopeQueriedForCollection($query, $collectionId)
    {
        return $query->whereJsonContains('queried_collection_ids', $collectionId);
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeOlderThan($query, $days = 30)
    {
        return $query->where('last_accessed_at', '<', now()->subDays($days));
    }

    public function scopeTitleContains($query, $search)
    {
        return $query->where('title', 'LIKE', "%{$search}%");
    }

    public function updateLastAccessed()
    {
        $this->update(['last_accessed_at' => now()]);
    }

    public function isInCollection($collectionId): bool
    {
        return in_array($collectionId, $this->collection_ids ?? []);
    }

    public function wasQueriedForCollection($collectionId): bool
    {
        return in_array($collectionId, $this->queried_collection_ids ?? []);
    }

    public function addQueriedCollection($collectionId): void
    {
        $queriedCollections = $this->queried_collection_ids ?? [];
        if (!in_array($collectionId, $queriedCollections)) {
            $queriedCollections[] = $collectionId;
            $this->update(['queried_collection_ids' => $queriedCollections]);
        }
    }

    public function hasTag($tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function isExpired($days = 30): bool
    {
        if (!$this->last_accessed_at) {
            return false;
        }

        return $this->last_accessed_at->lt(now()->subDays($days));
    }

    public function getPriceInDollars(): ?float
    {
        return $this->price ? $this->price / 100 : null;
    }

    public function getCompareAtPriceInDollars(): ?float
    {
        return $this->compare_at_price ? $this->compare_at_price / 100 : null;
    }

    public function getShopifyProductId(): string
    {
        return str_starts_with($this->product_id, 'gid://')
            ? $this->product_id
            : "gid://shopify/Product/{$this->product_id}";
    }

    public function getNumericProductId(): string
    {
        if (str_starts_with($this->product_id, 'gid://')) {
            return last(explode('/', $this->product_id));
        }
        return $this->product_id;
    }
}