<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SupportExpectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expectation',
        'replies',
        'status',
        'last_reply_at',
    ];

    protected $casts = [
        'expectation' => 'array',
        'replies' => 'array',
        'last_reply_at' => 'datetime',
    ];

    /**
     * Get the user that owns the support expectation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the latest reply from the replies array.
     */
    public function getLatestReplyAttribute()
    {
        if (!$this->replies || empty($this->replies)) {
            return null;
        }

        return collect($this->replies)->last();
    }

    /**
     * Add a new reply to the expectation.
     */
    public function addReply(string $message, string $adminEmail = 'admin@bestdecoders.com'): void
    {
        $replies = $this->replies ?? [];

        $replies[] = [
            'message' => $message,
            'admin_email' => $adminEmail,
            'created_at' => now()->toISOString(),
        ];

        $this->update([
            'replies' => $replies,
            'status' => 'replied',
            'last_reply_at' => now(),
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent expectations.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}