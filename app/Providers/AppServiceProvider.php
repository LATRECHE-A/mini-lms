<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * AppServiceProvider is responsible for registering application services and bootstrapping any necessary components.
 * In this case, it registers authorization policies for the Formation, Quiz, Note, and AI
 */

namespace App\Providers;

use App\Models\AiGeneration;
use App\Models\Formation;
use App\Models\Note;
use App\Models\Quiz;
use App\Policies\AiGenerationPolicy;
use App\Policies\FormationPolicy;
use App\Policies\NotePolicy;
use App\Policies\QuizPolicy;
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
        // Register authorization policies
        Gate::policy(Formation::class, FormationPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(AiGeneration::class, AiGenerationPolicy::class);
    }
}
