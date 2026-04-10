<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a quiz associated with a sub-chapter. 
 * It includes relationships to questions and quiz attempts, as well as scopes for published quizzes and a count of questions.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sub_chapter_id',
        'title',
        'description',
        'status',
    ];

    public function subChapter(): BelongsTo
    {
        return $this->belongsTo(SubChapter::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }
}
