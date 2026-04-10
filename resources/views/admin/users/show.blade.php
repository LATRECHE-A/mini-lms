@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>Utilisateurs</a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $user->email }} · {{ ucfirst($user->role) }} · Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Formations --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Formations ({{ $user->formations->count() }})</h2>
            </div>
            @forelse($user->formations as $formation)
            <div class="px-5 py-3 border-b border-slate-50 last:border-b-0">
                <a href="{{ route('admin.formations.show', $formation) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">{{ $formation->name }}</a>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucune formation.</div>
            @endforelse
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Notes</h2>
            </div>
            @forelse($user->notes as $note)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $note->subject }}</p>
                    <p class="text-xs text-slate-400">{{ $note->formation->name }}</p>
                </div>
                <span class="text-sm font-semibold text-{{ $note->grade_color }}-600">{{ $note->grade }}/20</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucune note.</div>
            @endforelse
        </div>

        {{-- Quiz History --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Historique des quiz ({{ $user->quizAttempts->count() }})</h2>
            </div>
            @forelse($user->quizAttempts->take(15) as $attempt)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $attempt->quiz->title }}</p>
                    <p class="text-xs text-slate-400">{{ $attempt->completed_at?->format('d/m/Y H:i') ?? 'En cours' }}</p>
                </div>
                @if($attempt->isCompleted())
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $attempt->grade_color }}-100 text-{{ $attempt->grade_color }}-800">
                    {{ $attempt->score }}/{{ $attempt->total_questions }} · {{ $attempt->percentage }}%
                </span>
                @endif
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucun quiz complété.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
