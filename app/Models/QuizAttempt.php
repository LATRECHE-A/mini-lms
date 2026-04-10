<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a user's attempt at a quiz, including their score, answers, and timestamps.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'total_questions',
        'percentage',
        'answers_given',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers_given' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function getGradeLabelAttribute(): string
    {
        return match (true) {
            $this->percentage >= 90 => 'Excellent',
            $this->percentage >= 75 => 'Très bien',
            $this->percentage >= 60 => 'Bien',
            $this->percentage >= 50 => 'Passable',
            default => 'Insuffisant',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match (true) {
            $this->percentage >= 90 => 'emerald',
            $this->percentage >= 75 => 'sky',
            $this->percentage >= 60 => 'amber',
            $this->percentage >= 50 => 'orange',
            default => 'rose',
        };
    }
}
