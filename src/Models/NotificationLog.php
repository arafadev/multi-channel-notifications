<?php


namespace Arafa\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'recipient',
        'channel',
        'title',
        'body',
        'data',
        'status',
        'message_id',
        'error',
        'provider_response',
        'attempts',
        'sent_at',
        'failed_at',
    ];
    
    protected $casts = [
        'data' => 'array',
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
    
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }
    
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
    
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
    
    public function scopeByRecipient($query, string $recipient)
    {
        return $query->where('recipient', $recipient);
    }
    
    public function scopeRecentDays($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    public function isSuccessful(): bool
    {
        return $this->status === 'sent';
    }
    
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}