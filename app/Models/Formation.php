<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'level', 'duration_hours', 'status', 'created_by',
    ];

    // Relationships

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('enrolled_at')->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Ownership

    public function isOwnedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function isCreatedByStudent(): bool
    {
        return $this->creator?->isStudent() ?? false;
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Accessors

    public function getLevelBadgeColorAttribute(): string
    {
        return match ($this->level) {
            'débutant' => 'emerald',
            'intermédiaire' => 'amber',
            'avancé' => 'rose',
            default => 'slate',
        };
    }
}
