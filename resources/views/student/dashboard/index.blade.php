@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Bonjour, {{ auth()->user()->name }} 👋</h1>
        <p class="text-sm text-slate-500 mt-1">Voici un résumé de votre progression</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Formations</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $enrolled_formations->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Quiz complétés</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $completed_quizzes }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Score moyen</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $avg_score }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- My formations --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Mes formations</h2>
                <a href="{{ route('student.formations.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Voir tout →</a>
            </div>
            @forelse($enrolled_formations as $formation)
            <a href="{{ route('student.formations.show', $formation) }}" class="block px-5 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $formation->name }}</p>
                        <p class="text-xs text-slate-400">{{ $formation->chapters_count }} chapitre(s) · {{ ucfirst($formation->level) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-sm text-slate-400">Vous n'êtes inscrit à aucune formation.</div>
            @endforelse
        </div>

        {{-- Recent quiz results --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Derniers résultats</h2>
                <a href="{{ route('student.quizzes.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Historique →</a>
            </div>
            @forelse($recent_attempts as $attempt)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $attempt->quiz->title }}</p>
                    <p class="text-xs text-slate-400">{{ $attempt->completed_at->diffForHumans() }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $attempt->grade_color }}-100 text-{{ $attempt->grade_color }}-800">
                    {{ $attempt->score }}/{{ $attempt->total_questions }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-slate-400">Aucun quiz complété.</div>
            @endforelse
        </div>

        {{-- Pending todos --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Tâches en cours</h2>
                <a href="{{ route('student.todos.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Toutes →</a>
            </div>
            @forelse($pending_todos as $todo)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <span class="text-sm text-slate-700">{{ $todo->title }}</span>
                @if($todo->due_date)
                <span class="text-xs {{ $todo->is_overdue ? 'text-rose-600 font-medium' : 'text-slate-400' }}">{{ $todo->due_date->format('d/m') }}</span>
                @endif
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Rien à faire !</div>
            @endforelse
        </div>

        {{-- Recent grades --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Dernières notes</h2>
                <a href="{{ route('student.notes.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Toutes →</a>
            </div>
            @forelse($recent_notes as $note)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $note->subject }}</p>
                    <p class="text-xs text-slate-400">{{ $note->formation->name }}</p>
                </div>
                <span class="font-semibold text-sm text-{{ $note->grade_color }}-600">{{ $note->grade }}/20</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucune note.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
