{{-- File: resources/views/student/flashcards/formation.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <a href="{{ route('student.flashcards.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Flashcards
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-900">{{ $formation->name }}</h1>
            @if($stats['due'] > 0)
            <a href="{{ route('student.flashcards.study', ['formation_id' => $formation->id]) }}"
               class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Réviser cette formation ({{ $stats['due'] }})
            </a>
            @endif
        </div>
    </div>

    {{-- Mini stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        <div class="bg-white rounded-lg border border-slate-200 p-3 text-center">
            <p class="text-lg font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400">Total</p>
        </div>
        <div class="bg-amber-50 rounded-lg border border-amber-200 p-3 text-center">
            <p class="text-lg font-bold text-amber-700">{{ $stats['due'] }}</p>
            <p class="text-xs text-amber-600">À réviser</p>
        </div>
        <div class="bg-sky-50 rounded-lg border border-sky-200 p-3 text-center">
            <p class="text-lg font-bold text-sky-700">{{ $stats['learning'] }}</p>
            <p class="text-xs text-sky-600">En cours</p>
        </div>
        <div class="bg-emerald-50 rounded-lg border border-emerald-200 p-3 text-center">
            <p class="text-lg font-bold text-emerald-700">{{ $stats['mastered'] }}</p>
            <p class="text-xs text-emerald-600">Maîtrisées</p>
        </div>
    </div>

    {{-- Chapter tree --}}
    <div class="space-y-4">
        @forelse($formation->chapters as $chapter)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 font-semibold text-xs">{{ $chapter->order }}</div>
                    <h3 class="font-medium text-slate-900">{{ $chapter->title }}</h3>
                </div>
            </div>

            @forelse($chapter->subChapters as $sub)
            @php
                $count = $cardCounts[$sub->id] ?? 0;
                $due   = $dueCounts[$sub->id] ?? 0;
            @endphp
            <a href="{{ route('student.flashcards.subchapter', $sub) }}"
               class="flex items-center justify-between px-5 py-3 border-b border-slate-50 last:border-b-0 hover:bg-slate-50 transition-colors group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-5 h-5 rounded bg-slate-100 flex items-center justify-center text-slate-400 text-xs flex-shrink-0">{{ $sub->order }}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600 transition-colors truncate">{{ $sub->title }}</p>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400 flex-wrap">
                            @if($count > 0)
                                <span>{{ $count }} carte(s)</span>
                                @if($due > 0)
                                    <span class="text-amber-600 font-medium">{{ $due }} à réviser</span>
                                @else
                                    <span class="text-emerald-600">À jour ✓</span>
                                @endif
                            @else
                                <span class="italic">Aucune flashcard</span>
                            @endif
                        </div>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            @empty
            <div class="px-5 py-4 text-sm text-slate-400 italic">Aucun sous-chapitre.</div>
            @endforelse
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Aucun chapitre dans cette formation.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
