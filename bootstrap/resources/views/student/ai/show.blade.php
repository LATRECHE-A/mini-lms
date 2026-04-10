@extends('layouts.app')

@section('content')
<div class="fade-in max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('student.ai.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>IA Playground</a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Contenu généré</h1>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $generation->status_color }}-100 text-{{ $generation->status_color }}-700">
                {{ $generation->status_label }}
            </span>
        </div>
        <div class="flex items-center gap-2 mt-2 text-sm text-slate-500">
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
        <a href="{{ route('student.ai.edit', $generation) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg px-4 py-2.5 hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Modifier
        </a>
        @endif

        <form method="POST" action="{{ route('student.ai.regenerate', $generation) }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 bg-white border border-brand-300 rounded-lg px-4 py-2.5 hover:bg-brand-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Régénérer
            </button>
        </form>

        <form method="POST" action="{{ route('student.ai.destroy', $generation) }}" onsubmit="return confirm('Supprimer cette génération ?')" class="inline">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 text-sm font-medium text-rose-600 bg-white border border-rose-200 rounded-lg px-4 py-2.5 hover:bg-rose-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Supprimer
            </button>
        </form>
    </div>

    @if($generation->isDraft())
    <div class="bg-sky-50 rounded-xl border border-sky-200 p-5 mb-8" x-data="{ confirmOpen: false }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-200 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sky-900">Valider et ajouter à mes formations</h3>
                    <p class="text-xs text-sky-700 mt-0.5">Crée une formation personnelle avec les chapitres et le quiz. Vous pourrez y accéder depuis « Mes formations ».</p>
                </div>
            </div>
            <button @click="confirmOpen = true"
                class="bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Valider
            </button>
        </div>

        @if(!$parsed['parsed'])
        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
            Contenu non structuré. Cliquez « Régénérer » pour obtenir un contenu importable.
        </div>
        @endif

        {{-- Confirmation --}}
        <div x-show="confirmOpen" x-transition x-cloak class="mt-5 pt-5 border-t border-sky-200">
            <div class="bg-white rounded-lg border border-sky-300 p-4">
                <p class="text-sm font-medium text-sky-900 mb-1">Êtes-vous sûr ?</p>
                <p class="text-xs text-sky-700 mb-4">Cette action va créer une formation personnelle avec les chapitres et le quiz. Le contenu sera verrouillé et vous ne pourrez plus le modifier.</p>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('student.ai.validate', $generation) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-sky-700 hover:bg-sky-800 text-white text-sm font-medium py-2 px-5 rounded-lg transition-colors">
                            Oui, valider et créer la formation
                        </button>
                    </form>
                    <button @click="confirmOpen = false" class="text-sm text-slate-500 hover:text-slate-700">Annuler</button>
                </div>
            </div>
        </div>
    </div>
    @elseif($generation->isValidated())
    <div class="bg-sky-50 rounded-xl border border-sky-200 p-4 mb-8 flex items-center gap-3">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <div>
            <p class="text-sm text-sky-800 font-medium">Contenu validé le {{ $generation->validated_at->format('d/m/Y à H:i') }}</p>
            <p class="text-xs text-sky-600 mt-0.5">Une formation a été créée dans « Mes formations ».</p>
        </div>
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
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-8">
            <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-amber-200 flex items-center justify-center text-amber-800 text-sm font-semibold">?</span>
                    <h3 class="font-semibold text-amber-900">{{ $parsed['quiz']['title'] }}</h3>
                    <span class="text-xs text-amber-600">{{ count($parsed['quiz']['questions']) }} questions</span>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($parsed['quiz']['questions'] as $qi => $q)
                <div class="px-6 py-4">
                    <p class="text-sm font-medium text-slate-800 mb-3">
                        <span class="text-brand-600 font-semibold">Q{{ $qi + 1 }}.</span> {{ $q['question'] }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ml-6">
                        @foreach($q['options'] as $oi => $option)
                        <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg bg-slate-50 text-slate-600">
                            <span class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center flex-shrink-0 text-xs font-medium text-slate-400">{{ chr(65 + $oi) }}</span>
                            {{ $option }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200">
                <p class="text-xs text-slate-400 italic">Validez ce contenu pour passer le quiz dans « Mes formations ».</p>
            </div>
        </div>
        @endif
    @else
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-sm text-amber-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                Contenu non structuré. Cliquez « Régénérer » pour un meilleur affichage.
            </div>
            <div class="prose prose-sm prose-slate max-w-none whitespace-pre-wrap">{{ $generation->generated_content }}</div>
        </div>
    @endif
</div>
@endsection
