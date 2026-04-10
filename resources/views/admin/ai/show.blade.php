@extends('layouts.app')

@section('content')
<div class="fade-in max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.ai.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>IA Playground</a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Contenu généré</h1>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $generation->status_color }}-100 text-{{ $generation->status_color }}-700">
                {{ $generation->status_label }}
            </span>
        </div>
        <div class="flex items-center gap-2 mt-2 text-sm text-slate-500">
            <span>Par {{ $generation->user->name }}</span>
            <span>·</span>
            <span>Créé le {{ $generation->created_at->format('d/m/Y à H:i') }}</span>
            @if($generation->updated_at->gt($generation->created_at->addSeconds(5)))
                <span>·</span>
                <span class="text-amber-600">Modifié le {{ $generation->updated_at->format('d/m/Y à H:i') }}</span>
            @endif
            <span>·</span>
            <span class="capitalize">{{ $generation->type }}</span>
        </div>
    </div>

    {{-- Prompt --}}
    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 mb-6">
        <p class="text-xs text-slate-400 mb-1">Prompt utilisé</p>
        <p class="text-sm text-slate-700">{{ $generation->prompt }}</p>
    </div>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if($generation->isEditable() && $parsed['parsed'])
        <a href="{{ route('admin.ai.edit', $generation) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg px-4 py-2.5 hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Modifier
        </a>
        @endif

        @if($generation->isOwnedBy(auth()->user()))
        <form method="POST" action="{{ route('admin.ai.regenerate', $generation) }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 bg-white border border-brand-300 rounded-lg px-4 py-2.5 hover:bg-brand-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Régénérer
            </button>
        </form>
        @endif

        <form method="POST" action="{{ route('admin.ai.destroy', $generation) }}" onsubmit="return confirm('Supprimer cette génération ?')" class="inline">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 text-sm font-medium text-rose-600 bg-white border border-rose-200 rounded-lg px-4 py-2.5 hover:bg-rose-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Supprimer
            </button>
        </form>
    </div>

    @if($generation->isDraft() && $generation->isOwnedBy(auth()->user()))
    <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-5 mb-8" x-data="{ showForm: false }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-200 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-emerald-900">Importer dans les formations</h3>
                    <p class="text-xs text-emerald-700 mt-0.5">Crée une formation avec chapitres, sous-chapitres et quiz.</p>
                </div>
            </div>
            <button @click="showForm = !showForm" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors shadow-sm">
                Importer
            </button>
        </div>

        <form method="POST" action="{{ route('admin.ai.import', $generation) }}" x-show="showForm" x-transition class="mt-5 pt-5 border-t border-emerald-200 space-y-4">
            @csrf
            @if(!$parsed['parsed'])
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                Contenu non structuré. Cliquez « Régénérer » pour obtenir un contenu importable.
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-emerald-800 mb-1">Nom de la formation</label>
                    <input type="text" name="formation_name" value="{{ $parsed['chapter_title'] ?? '' }}" class="w-full px-3 py-2.5 rounded-lg border border-emerald-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-emerald-800 mb-1">Niveau</label>
                    <select name="level" class="w-full px-3 py-2.5 rounded-lg border border-emerald-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="débutant">Débutant</option>
                        <option value="intermédiaire">Intermédiaire</option>
                        <option value="avancé">Avancé</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-emerald-800 mb-1">Statut</label>
                    <select name="status" class="w-full px-3 py-2.5 rounded-lg border border-emerald-300 text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">
                Créer la formation
            </button>
            @endif
        </form>
    </div>
    @elseif($generation->isPublished())
    <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 mb-8 flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <p class="text-sm text-emerald-800 font-medium">Ce contenu a été importé dans une formation.</p>
    </div>
    @elseif($generation->isValidated() && $generation->isStudentContent())
    <div class="bg-sky-50 rounded-xl border border-sky-200 p-4 mb-8 flex items-center gap-3">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <p class="text-sm text-sky-800 font-medium">Contenu validé par {{ $generation->user->name }} le {{ $generation->validated_at?->format('d/m/Y à H:i') }}.</p>
    </div>
    @endif

    @if($parsed['parsed'])
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">{{ $parsed['chapter_title'] }}</h2>
        </div>

        @if(!empty($parsed['subchapters']))
        <div class="space-y-4 mb-8">
            @foreach($parsed['subchapters'] as $i => $sub)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center text-brand-700 text-sm font-semibold">{{ $i + 1 }}</span>
                        <h3 class="font-semibold text-slate-900">{{ $sub['title'] }}</h3>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="prose prose-sm prose-slate max-w-none">{!! $sub['content'] !!}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($parsed['quiz'])
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-8" x-data="{ showAnswers: false }">
            <div class="px-6 py-4 bg-amber-50 border-b border-amber-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-amber-200 flex items-center justify-center text-amber-800 text-sm font-semibold">?</span>
                    <h3 class="font-semibold text-amber-900">{{ $parsed['quiz']['title'] }}</h3>
                    <span class="text-xs text-amber-600">{{ count($parsed['quiz']['questions']) }} questions</span>
                </div>
                <button @click="showAnswers = !showAnswers" class="text-xs text-amber-700 hover:text-amber-900 font-medium border border-amber-300 rounded-lg px-3 py-1.5 hover:bg-amber-100 transition-colors">
                    <span x-text="showAnswers ? 'Masquer les réponses' : 'Afficher les réponses'"></span>
                </button>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($parsed['quiz']['questions'] as $qi => $q)
                <div class="px-6 py-4">
                    <p class="text-sm font-medium text-slate-800 mb-3">
                        <span class="text-brand-600 font-semibold">Q{{ $qi + 1 }}.</span> {{ $q['question'] }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ml-6">
                        @foreach($q['options'] as $oi => $option)
                        <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg transition-colors"
                            :class="showAnswers && {{ $oi === $q['correct_index'] ? 'true' : 'false' }} ? 'bg-emerald-50 text-emerald-800 font-medium ring-1 ring-emerald-300' : 'bg-slate-50 text-slate-600'">
                            <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 text-xs font-medium transition-colors"
                                :class="showAnswers && {{ $oi === $q['correct_index'] ? 'true' : 'false' }} ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 text-slate-400'">
                                <span x-show="!(showAnswers && {{ $oi === $q['correct_index'] ? 'true' : 'false' }})">{{ chr(65 + $oi) }}</span>
                                <svg x-show="showAnswers && {{ $oi === $q['correct_index'] ? 'true' : 'false' }}" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </span>
                            {{ $option }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @else
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-sm text-amber-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                Contenu non structuré. Cliquez « Régénérer » pour obtenir un contenu structuré.
            </div>
            <div class="prose prose-sm prose-slate max-w-none whitespace-pre-wrap">{{ $generation->generated_content }}</div>
        </div>
    @endif
</div>
@endsection
