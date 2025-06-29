<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'sent_at',
        'read_at',
        'is_important'
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'is_important' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope per notifiche non lette
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope per notifiche importanti
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Verifica se la notifica è stata letta
     */
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }
}
