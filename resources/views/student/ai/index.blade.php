@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">IA Playground</h1>
            <p class="text-sm text-slate-500 mt-1">Générez et gérez votre contenu d'étude personnalisé</p>
        </div>
        <a href="{{ route('student.ai.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvelle génération
        </a>
    </div>

    @if(!$isAvailable)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6 text-sm">Service IA non disponible.</div>
    @endif

    <div class="mb-10">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Mes brouillons</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $drafts->count() }}</span>
        </div>

        @if($drafts->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-400">Aucun brouillon. Générez du contenu pour commencer.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($drafts as $gen)
                <a href="{{ route('student.ai.show', $gen) }}" class="block bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 group-hover:text-brand-700 truncate">{{ Str::limit($gen->prompt, 80) }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                <span class="capitalize">{{ $gen->type }}</span>
                                <span>{{ $gen->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Brouillon</span>
                            <span class="text-xs text-brand-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Modifier →</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Ma bibliothèque validée</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">{{ $validated->count() }}</span>
        </div>

        @if($validated->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-400">Aucun contenu validé. Générez et validez du contenu pour le retrouver ici.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($validated as $gen)
                <a href="{{ route('student.ai.show', $gen) }}" class="block bg-white rounded-xl border border-sky-200 p-5 hover:border-sky-300 hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 group-hover:text-sky-700 truncate">{{ Str::limit($gen->prompt, 80) }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                <span class="capitalize">{{ $gen->type }}</span>
                                <span>Validé {{ $gen->validated_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                Validé
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
