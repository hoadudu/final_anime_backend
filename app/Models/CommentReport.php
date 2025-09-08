<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'comment_id',
        'reason',
        'description',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function markAsResolved(User $admin): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);
    }

    public function markAsDismissed(User $admin): void
    {
        $this->update([
            'status' => 'dismissed',
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);
    }

    public function getFormattedReasonAttribute(): string
    {
        return match($this->reason) {
            'spam' => 'Spam',
            'inappropriate' => 'Inappropriate Content',
            'harassment' => 'Harassment',
            'other' => 'Other',
            default => ucfirst($this->reason),
        };
    }
}
