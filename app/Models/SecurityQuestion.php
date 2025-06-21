<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class SecurityQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question',
        'answer_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setAnswerAttribute($value): void
    {
        $this->attributes['answer_hash'] = Hash::make(strtolower(trim($value)));
    }

    public function checkAnswer(string $answer): bool
    {
        return Hash::check(strtolower(trim($answer)), $this->answer_hash);
    }
}