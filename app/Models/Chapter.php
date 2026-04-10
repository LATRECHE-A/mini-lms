<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a chapter in a formation, including relationships to the formation
 * and its sub-chapters, as well as fields for images, sources, and Mermaid diagrams.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'title',
        'description',
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

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    public function subChapters(): HasMany
    {
        return $this->hasMany(SubChapter::class)->orderBy('order');
    }
}
