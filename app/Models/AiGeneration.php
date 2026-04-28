<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * AI generation row.
 *
 * Lifecycle:
 *   draft     - just generated, editable.
 *   published - admin imported it as a Formation (link via formation_id).
 *   validated - student validated it as their own Formation (link via
 *               formation_id; user is auto-enrolled; created_by is set).
 *
 * `type` ∈ {course, quiz, mixed, full}. The 'full' marker tells the
 * importer to also seed flashcards for every sub-chapter.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiGeneration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'formation_id',
        'prompt',
        'generated_content',
        'type',
        'status',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
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

    // Convenience predicates
    public function hasFormation(): bool
    {
        return $this->formation_id !== null;
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated' || $this->status === 'published';
    }

    // Scopes
    public function scopeByUser(Builder $q, int $id): Builder
    {
        return $q->where('user_id', $id);
    }

    public function scopeDraft(Builder $q): Builder
    {
        return $q->where('status', 'draft');
    }

    public function scopeValidated(Builder $q): Builder
    {
        return $q->whereIn('status', ['validated', 'published']);
    }

    public function scopeAdminOwned(Builder $q): Builder
    {
        return $q->whereHas('user', fn ($u) => $u->where('role', 'admin'));
    }

    public function scopeStudentOwned(Builder $q): Builder
    {
        return $q->whereHas('user', fn ($u) => $u->where('role', 'apprenant'));
    }
}
