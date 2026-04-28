<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Registers all authorization policies.
 */

namespace App\Providers;

use App\Models\AiGeneration;
use App\Models\Flashcard;
use App\Models\Formation;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\SubChapter;
use App\Policies\AiGenerationPolicy;
use App\Policies\FlashcardPolicy;
use App\Policies\FormationPolicy;
use App\Policies\NotePolicy;
use App\Policies\QuizPolicy;
use App\Policies\SubChapterPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Formation::class, FormationPolicy::class);
        Gate::policy(SubChapter::class, SubChapterPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(AiGeneration::class, AiGenerationPolicy::class);
        Gate::policy(Flashcard::class, FlashcardPolicy::class);
    }
}
