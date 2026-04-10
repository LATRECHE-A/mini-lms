<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a personal note taken by a user for a specific sub-chapter.
 * Each note belongs to a user and a sub-chapter, allowing users to organize their notes effectively.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sub_chapter_id',
        'title',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subChapter(): BelongsTo
    {
        return $this->belongsTo(SubChapter::class);
    }
}
