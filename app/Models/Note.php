<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a Note, which is associated with a User and a Formation.
 * It includes attributes for the subject, grade, and an optional comment.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'formation_id',
        'subject',
        'grade',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function getGradeColorAttribute(): string
    {
        return match (true) {
            $this->grade >= 16 => 'emerald',
            $this->grade >= 12 => 'sky',
            $this->grade >= 10 => 'amber',
            default => 'rose',
        };
    }
}
