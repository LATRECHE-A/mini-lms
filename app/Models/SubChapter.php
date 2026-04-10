<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a SubChapter in the LMS.
 * Each SubChapter belongs to a Chapter, may have one Quiz, and can have many PersonalNotes.
 * Includes fields for enriched image data, sources, and Mermaid diagram code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubChapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'content',
        'order',
        'image_url',
        'image_alt',
        'image_credit',
        'sources',
        'mermaid_code',
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'array',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function personalNotes(): HasMany
    {
        return $this->hasMany(PersonalNote::class);
    }

    public function getFormationAttribute()
    {
        return $this->chapter?->formation;
    }
}
