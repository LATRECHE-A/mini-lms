<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Model representing a user in the system, with roles (admin or student)
 * and relationships to formations, quiz attempts, notes, personal notes, todos, AI generations, flashcards, and activity logs.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'apprenant';
    }

    // Relationships
    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class)->withPivot('enrolled_at')->withTimestamps();
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function personalNotes(): HasMany
    {
        return $this->hasMany(PersonalNote::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    // Scopes
    public function scopeStudents($query)
    {
        return $query->where('role', 'apprenant');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
