<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * This file defines all the web routes for the Mini LMS application, including authentication, admin, and student routes. 
 * It uses route groups and middleware to organize routes based on user roles and authentication state, ensuring a clean and maintainable routing structure.
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;

// Root - smart redirect based on auth state

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('student.dashboard');
    }
    return redirect()->route('login');
});

// Auth Routes (guest only)

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin Routes

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('formations', Admin\FormationController::class);
        Route::post('formations/{formation}/enroll', [Admin\FormationController::class, 'enroll'])->name('formations.enroll');
        Route::delete('formations/{formation}/unenroll/{user}', [Admin\FormationController::class, 'unenroll'])->name('formations.unenroll');

        Route::get('formations/{formation}/chapters/create', [Admin\ChapterController::class, 'create'])->name('chapters.create');
        Route::post('formations/{formation}/chapters', [Admin\ChapterController::class, 'store'])->name('chapters.store');
        Route::get('formations/{formation}/chapters/{chapter}/edit', [Admin\ChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('formations/{formation}/chapters/{chapter}', [Admin\ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('formations/{formation}/chapters/{chapter}', [Admin\ChapterController::class, 'destroy'])->name('chapters.destroy');

        Route::get('chapters/{chapter}/subchapters/create', [Admin\SubChapterController::class, 'create'])->name('subchapters.create');
        Route::post('chapters/{chapter}/subchapters', [Admin\SubChapterController::class, 'store'])->name('subchapters.store');
        Route::get('chapters/{chapter}/subchapters/{subchapter}', [Admin\SubChapterController::class, 'show'])->name('subchapters.show');
        Route::get('chapters/{chapter}/subchapters/{subchapter}/edit', [Admin\SubChapterController::class, 'edit'])->name('subchapters.edit');
        Route::put('chapters/{chapter}/subchapters/{subchapter}', [Admin\SubChapterController::class, 'update'])->name('subchapters.update');
        Route::delete('chapters/{chapter}/subchapters/{subchapter}', [Admin\SubChapterController::class, 'destroy'])->name('subchapters.destroy');

        Route::get('quizzes', [Admin\QuizController::class, 'index'])->name('quizzes.index');
        Route::get('subchapters/{subchapter}/quizzes/create', [Admin\QuizController::class, 'create'])->name('quizzes.create');
        Route::post('subchapters/{subchapter}/quizzes', [Admin\QuizController::class, 'store'])->name('quizzes.store');
        Route::get('quizzes/{quiz}', [Admin\QuizController::class, 'show'])->name('quizzes.show');
        Route::get('quizzes/{quiz}/edit', [Admin\QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('quizzes/{quiz}', [Admin\QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('quizzes/{quiz}', [Admin\QuizController::class, 'destroy'])->name('quizzes.destroy');

        Route::get('quizzes/{quiz}/questions/create', [Admin\QuestionController::class, 'create'])->name('questions.create');
        Route::post('quizzes/{quiz}/questions', [Admin\QuestionController::class, 'store'])->name('questions.store');
        Route::get('quizzes/{quiz}/questions/{question}/edit', [Admin\QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('quizzes/{quiz}/questions/{question}', [Admin\QuestionController::class, 'update'])->name('questions.update');
        Route::delete('quizzes/{quiz}/questions/{question}', [Admin\QuestionController::class, 'destroy'])->name('questions.destroy');

        Route::resource('users', Admin\UserController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::resource('notes', Admin\NoteController::class)->except('show');

        // Admin AI
        Route::get('ai', [Admin\AIController::class, 'index'])->name('ai.index');
        Route::get('ai/create', [Admin\AIController::class, 'create'])->name('ai.create');
        Route::post('ai/generate', [Admin\AIController::class, 'generate'])->name('ai.generate')->middleware('throttle:10,1');
        Route::get('ai/{generation}', [Admin\AIController::class, 'show'])->name('ai.show');
        Route::get('ai/{generation}/edit', [Admin\AIController::class, 'edit'])->name('ai.edit');
        Route::put('ai/{generation}', [Admin\AIController::class, 'update'])->name('ai.update');
        Route::post('ai/{generation}/import', [Admin\AIController::class, 'import'])->name('ai.import');
        Route::post('ai/{generation}/regenerate', [Admin\AIController::class, 'regenerate'])->name('ai.regenerate')->middleware('throttle:10,1');
        Route::delete('ai/{generation}', [Admin\AIController::class, 'destroy'])->name('ai.destroy');
    });

// Student Routes

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:apprenant'])
    ->group(function () {

        Route::get('/dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/formations', [Student\FormationController::class, 'index'])->name('formations.index');
        Route::get('/formations/{formation}', [Student\FormationController::class, 'show'])->name('formations.show');
        Route::get('/formations/{formation}/subchapters/{subchapter}', [Student\FormationController::class, 'showSubChapter'])->name('formations.subchapter');

        Route::get('/quizzes', [Student\QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/{quiz}', [Student\QuizController::class, 'show'])->name('quizzes.show');
        Route::post('/quizzes/{quiz}/submit', [Student\QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('/quizzes/attempts/{attempt}', [Student\QuizController::class, 'result'])->name('quizzes.result');

        Route::get('/notes', [Student\NoteController::class, 'index'])->name('notes.index');
        Route::post('/notes', [Student\NoteController::class, 'store'])->name('notes.store');
        Route::put('/notes/{note}', [Student\NoteController::class, 'update'])->name('notes.update');
        Route::delete('/notes/{note}', [Student\NoteController::class, 'destroy'])->name('notes.destroy');

        Route::get('/todos', [Student\TodoController::class, 'index'])->name('todos.index');
        Route::post('/todos', [Student\TodoController::class, 'store'])->name('todos.store');
        Route::patch('/todos/{todo}/toggle', [Student\TodoController::class, 'toggle'])->name('todos.toggle');
        Route::delete('/todos/{todo}', [Student\TodoController::class, 'destroy'])->name('todos.destroy');

        // Student AI - full CRUD + validate + regenerate
        Route::get('/ai', [Student\AIController::class, 'index'])->name('ai.index');
        Route::get('/ai/create', [Student\AIController::class, 'create'])->name('ai.create');
        Route::post('/ai/generate', [Student\AIController::class, 'generate'])->name('ai.generate')->middleware('throttle:10,1');
        Route::get('/ai/{generation}', [Student\AIController::class, 'show'])->name('ai.show');
        Route::get('/ai/{generation}/edit', [Student\AIController::class, 'edit'])->name('ai.edit');
        Route::put('/ai/{generation}', [Student\AIController::class, 'update'])->name('ai.update');
        Route::post('/ai/{generation}/validate', [Student\AIController::class, 'validate'])->name('ai.validate');
        Route::post('/ai/{generation}/regenerate', [Student\AIController::class, 'regenerate'])->name('ai.regenerate')->middleware('throttle:10,1');
        Route::delete('/ai/{generation}', [Student\AIController::class, 'destroy'])->name('ai.destroy');
    });
