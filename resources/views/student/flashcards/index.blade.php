{{-- resources/views/student/flashcards/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mes Flashcards</h1>
            <p class="text-sm text-slate-500 mt-1">Révisez vos connaissances avec la répétition espacée.</p>
        </div>
        @if($stats['due'] > 0)
        <a href="{{ route('student.flashcards.study') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors inline-flex items-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Tout réviser ({{ $stats['due'] }})
        </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Total</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-700">{{ $stats['due'] }}</p>
            <p class="text-xs text-amber-600 mt-1">À réviser</p>
        </div>
        <div class="bg-sky-50 rounded-xl border border-sky-200 p-4 text-center">
            <p class="text-2xl font-bold text-sky-700">{{ $stats['learning'] }}</p>
            <p class="text-xs text-sky-600 mt-1">En cours</p>
        </div>
        <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $stats['mastered'] }}</p>
            <p class="text-xs text-emerald-600 mt-1">Maîtrisées</p>
        </div>
    </div>

    {{-- Formations --}}
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Mes formations</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($formations as $formation)
        <a href="{{ route('student.flashcards.formation', $formation) }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition-all group">
            <div class="flex items-start justify-between mb-3">
                <h3 class="font-medium text-slate-900 group-hover:text-brand-700 transition-colors">{{ $formation->name }}</h3>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400 mb-3">
                <span>{{ $formation->chapters_count }} chapitre(s)</span>
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full {{ $formation->card_count > 0 ? 'bg-brand-400' : 'bg-slate-300' }}"></span>
                    {{ $formation->card_count }} carte(s)
                </span>
            </div>
            @if($formation->due_count > 0)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-amber-100 text-amber-700">{{ $formation->due_count }} à réviser</span>
            @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700">À jour ✓</span>
            @endif
        </a>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Vous n'êtes inscrit à aucune formation.</p>
            <p class="text-xs text-slate-400 mt-1">Les flashcards apparaîtront ici quand vous serez inscrit.</p>
        </div>
        @endforelse
    </div>

    {{-- Add personal card (not tied to any subchapter) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5" x-data="{ open: false }">
        <button @click="open = !open" class="text-sm text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Créer une flashcard libre
        </button>
        <form method="POST" action="{{ route('student.flashcards.store') }}" x-show="open" x-transition class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Question</label>
                <textarea name="question" rows="2" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500" placeholder="Qu'est-ce que..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Réponse</label>
                <textarea name="answer" rows="2" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500" placeholder="C'est..."></textarea>
            </div>
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">Créer</button>
        </form>
    </div>
</div>
@endsection
