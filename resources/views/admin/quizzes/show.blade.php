@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('admin.formations.show', $quiz->subChapter->chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                {{ $quiz->subChapter->chapter->formation->name }}
            </a>
            <h1 class="text-2xl font-bold text-slate-900">{{ $quiz->title }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $quiz->subChapter->chapter->title }} → {{ $quiz->subChapter->title }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $quiz->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $quiz->status === 'published' ? 'Publié' : 'Brouillon' }}
            </span>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-3 py-2 hover:bg-slate-50 transition-colors">Modifier</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Questions --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Questions ({{ $quiz->questions->count() }})</h2>
                <a href="{{ route('admin.questions.create', $quiz) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajouter une question
                </a>
            </div>

            @forelse($quiz->questions as $i => $question)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 text-sm font-semibold flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                        <p class="text-sm font-medium text-slate-800">{{ $question->question_text }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0 ml-4">
                        <a href="{{ route('admin.questions.edit', [$quiz, $question]) }}" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.questions.destroy', [$quiz, $question]) }}" onsubmit="return confirm('Supprimer cette question ?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-rose-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </form>
                    </div>
                </div>
                <div class="ml-10 space-y-1.5">
                    @foreach($question->answers as $answer)
                    <div class="flex items-center gap-2 text-sm {{ $answer->is_correct ? 'text-emerald-700 font-medium' : 'text-slate-600' }}">
                        @if($answer->is_correct)
                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        @else
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300"></div>
                        @endif
                        {{ $answer->answer_text }}
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-400 mb-2">Aucune question ajoutée.</p>
                <a href="{{ route('admin.questions.create', $quiz) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Ajouter la première question →</a>
            </div>
            @endforelse
        </div>

        {{-- Recent attempts --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Dernières tentatives</h2>
            </div>
            @forelse($quiz->attempts as $attempt)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $attempt->user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $attempt->completed_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="text-sm font-medium text-{{ $attempt->grade_color }}-600">{{ $attempt->score }}/{{ $attempt->total_questions }}</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucune tentative.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
