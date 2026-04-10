@extends('layouts.app')

@section('content')
<div class="fade-in max-w-3xl mx-auto">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-slate-900">Résultat du quiz</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $attempt->quiz->title }}</p>
    </div>

    {{-- Score Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center mb-8">
        <div class="w-24 h-24 rounded-full bg-{{ $attempt->grade_color }}-100 flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl font-bold text-{{ $attempt->grade_color }}-600">{{ $attempt->percentage }}%</span>
        </div>
        <p class="text-lg font-semibold text-slate-900">{{ $attempt->grade_label }}</p>
        <p class="text-sm text-slate-500 mt-1">{{ $attempt->score }} bonne(s) réponse(s) sur {{ $attempt->total_questions }}</p>
        <p class="text-xs text-slate-400 mt-2">{{ $attempt->completed_at->format('d/m/Y à H:i') }}</p>
    </div>

    {{-- Answers Review --}}
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Détail des réponses</h2>
    <div class="space-y-4">
        @foreach($attempt->quiz->questions as $i => $question)
        @php
            $givenData = $attempt->answers_given[$question->id] ?? null;
            $isCorrect = $givenData['is_correct'] ?? false;
            $submittedId = $givenData['submitted'] ?? null;
        @endphp
        <div class="bg-white rounded-xl border {{ $isCorrect ? 'border-emerald-200' : 'border-rose-200' }} p-5">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 {{ $isCorrect ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                    @if($isCorrect)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    @else
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    @endif
                </div>
                <p class="text-sm font-medium text-slate-800">{{ $question->question_text }}</p>
            </div>
            <div class="ml-10 space-y-1.5">
                @foreach($question->answers as $answer)
                <div class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg
                    {{ $answer->is_correct ? 'bg-emerald-50 text-emerald-700 font-medium' : '' }}
                    {{ $answer->id == $submittedId && !$answer->is_correct ? 'bg-rose-50 text-rose-700 line-through' : '' }}
                    {{ !$answer->is_correct && $answer->id != $submittedId ? 'text-slate-500' : '' }}">
                    @if($answer->is_correct)
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    @elseif($answer->id == $submittedId)
                        <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    @else
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300"></div>
                    @endif
                    {{ $answer->answer_text }}
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
        <a href="{{ route('student.quizzes.show', $attempt->quiz) }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">
            Réessayer le quiz
        </a>
        <a href="{{ route('student.formations.show', $attempt->quiz->subChapter->chapter->formation) }}" class="text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-4 py-2.5 hover:bg-slate-50 transition-colors">
            Retour à la formation
        </a>
    </div>
</div>
@endsection
