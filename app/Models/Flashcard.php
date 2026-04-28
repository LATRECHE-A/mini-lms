<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Flashcard with SM-2 spaced-repetition fields.
 *
 * - is_template = true  : admin's master copy, distributed (cloned) to
 *                         students on enrollment. Not used for studying.
 * - is_template = false : a personal, studyable copy. Both admin and
 *                         student have these - the admin's are seeded
 *                         alongside their templates so the admin can test
 *                         the cards.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'sub_chapter_id', 'question', 'answer', 'is_template',
        'difficulty', 'next_review_at', 'interval_days',
        'ease_factor', 'review_count', 'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'next_review_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'ease_factor' => 'decimal:2',
            'is_template' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subChapter(): BelongsTo
    {
        return $this->belongsTo(SubChapter::class);
    }

    // Accessors
    public function getFormationAttribute(): ?Formation
    {
        return $this->subChapter?->chapter?->formation;
    }

    public function getChapterAttribute(): ?Chapter
    {
        return $this->subChapter?->chapter;
    }

    // Scopes
    public function scopeTemplates(Builder $q): Builder
    {
        return $q->where('is_template', true);
    }

    public function scopePersonal(Builder $q): Builder
    {
        return $q->where('is_template', false);
    }

    public function scopeByUser(Builder $q, int $id): Builder
    {
        return $q->where('user_id', $id);
    }

    public function scopeDueForReview(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId)
            ->where('is_template', false)
            ->where(fn ($sub) => $sub->whereNull('next_review_at')->orWhere('next_review_at', '<=', now()))
            ->orderByRaw('next_review_at IS NULL DESC')
            ->orderBy('next_review_at');
    }

    public function scopeForFormation(Builder $q, int $formationId): Builder
    {
        return $q->whereHas('subChapter.chapter', fn ($c) => $c->where('formation_id', $formationId));
    }

    public function scopeForSubChapter(Builder $q, int $subChapterId): Builder
    {
        return $q->where('sub_chapter_id', $subChapterId);
    }
}
