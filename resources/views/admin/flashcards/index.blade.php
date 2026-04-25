{{-- resources/views/admin/flashcards/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Flashcards</h1>
            <p class="text-sm text-slate-500 mt-1">Créez des flashcards par formation. Elles seront automatiquement distribuées aux apprenants inscrits.</p>
        </div>
        @if($stats['due'] > 0)
        <a href="{{ route('admin.flashcards.study') }}" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors inline-flex items-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Réviser ({{ $stats['due'] }})
        </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Mes cartes</p>
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

    {{-- Formations list --}}
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Par formation</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($formations as $formation)
        <a href="{{ route('admin.flashcards.formation', $formation) }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition-all group">
            <div class="flex items-start justify-between mb-3">
                <h3 class="font-medium text-slate-900 group-hover:text-brand-700 transition-colors">{{ $formation->name }}</h3>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>{{ $formation->chapters_count }} chapitre(s)</span>
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full {{ $formation->template_count > 0 ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                    {{ $formation->template_count }} flashcard(s)
                </span>
            </div>
        </a>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Aucune formation. Créez-en une d'abord.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
