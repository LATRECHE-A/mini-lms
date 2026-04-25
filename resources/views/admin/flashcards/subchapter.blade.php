{{-- resources/views/admin/flashcards/subchapter.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('admin.flashcards.formation', $subchapter->chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $subchapter->chapter->formation->name }} → {{ $subchapter->chapter->title }}
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-900">{{ $subchapter->title }}</h1>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.flashcards.generate', $subchapter) }}">
                    @csrf
                    <button type="submit" class="bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-sm font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center gap-1.5"
                        onclick="this.disabled=true; this.innerHTML='Génération...'; this.form.submit();">
                        ⚡ Générer par IA
                    </button>
                </form>
                <a href="{{ route('admin.flashcards.study', ['sub_chapter_id' => $subchapter->id]) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Étudier →</a>
            </div>
        </div>
    </div>

    {{-- Add manually --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6" x-data="{ open: false }">
        <button @click="open = !open" class="text-sm text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Ajouter une flashcard
        </button>
        <form method="POST" action="{{ route('admin.flashcards.store') }}" x-show="open" x-transition class="mt-4 space-y-3">
            @csrf
            <input type="hidden" name="sub_chapter_id" value="{{ $subchapter->id }}">
            <input type="hidden" name="is_template" value="1">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Question</label>
                <textarea name="question" rows="2" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Réponse</label>
                <textarea name="answer" rows="2" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500"></textarea>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">Créer</button>
                <label class="inline-flex items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" name="is_template" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Template (distribué aux apprenants)
                </label>
            </div>
        </form>
    </div>

    {{-- Template flashcards --}}
    @if($templates->count() > 0)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
            Templates ({{ $templates->count() }})
            <span class="text-xs font-normal text-slate-400">— distribuées automatiquement aux apprenants inscrits</span>
        </h2>
        <div class="space-y-3">
            @foreach($templates as $card)
            <div class="bg-white rounded-xl border border-brand-100 p-5" x-data="{ editing: false }">
                <div x-show="!editing" class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $card->question }}</p>
                        <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">{{ $card->answer }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button @click="editing = true" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.flashcards.destroy', $card) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-rose-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </form>
                    </div>
                </div>
                <form x-show="editing" x-transition method="POST" action="{{ route('admin.flashcards.update', $card) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <textarea name="question" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">{{ $card->question }}</textarea>
                    <textarea name="answer" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">{{ $card->answer }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm py-1.5 px-4 rounded-lg">Enregistrer</button>
                        <button type="button" @click="editing = false" class="text-sm text-slate-500">Annuler</button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Personal flashcards --}}
    @if($personal->count() > 0)
    <div>
        <h2 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            Mes cartes personnelles ({{ $personal->count() }})
        </h2>
        <div class="space-y-3">
            @foreach($personal as $card)
            <div class="bg-white rounded-xl border border-slate-200 p-5" x-data="{ editing: false }">
                <div x-show="!editing" class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $card->question }}</p>
                        <p class="text-sm text-slate-500 mt-1.5">{{ $card->answer }}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                            <span>{{ $card->review_count }} révision(s)</span>
                            @if($card->next_review_at)<span>Prochaine : {{ $card->next_review_at->diffForHumans() }}</span>@else <span class="text-amber-500">Nouvelle</span>@endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button @click="editing = true" class="text-slate-400 hover:text-slate-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                        <form method="POST" action="{{ route('admin.flashcards.destroy', $card) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button class="text-slate-400 hover:text-rose-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></form>
                    </div>
                </div>
                <form x-show="editing" x-transition method="POST" action="{{ route('admin.flashcards.update', $card) }}" class="space-y-3">@csrf @method('PUT')
                    <textarea name="question" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">{{ $card->question }}</textarea>
                    <textarea name="answer" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">{{ $card->answer }}</textarea>
                    <div class="flex gap-2"><button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm py-1.5 px-4 rounded-lg">Enregistrer</button><button type="button" @click="editing = false" class="text-sm text-slate-500">Annuler</button></div>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($templates->isEmpty() && $personal->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
        <p class="text-sm text-slate-400 mb-3">Aucune flashcard pour ce sous-chapitre.</p>
        <p class="text-xs text-slate-400">Cliquez « Générer par IA » ou ajoutez-en manuellement.</p>
    </div>
    @endif
</div>
@endsection
