{{-- File: resources/views/student/formations/show.blade.php --}}
@extends('layouts.app')

@section('content')
@php
    $owned = $formation->isOwnedBy(auth()->user());
@endphp

<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div class="min-w-0">
            <a href="{{ route('student.formations.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Mes formations
            </a>
            <h1 class="text-2xl font-bold text-slate-900 break-words">{{ $formation->name }}</h1>
            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700">{{ ucfirst($formation->level) }}</span>
                @if($owned)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-violet-100 text-violet-700">Créée par moi</span>
                @endif
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $formation->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $formation->status === 'published' ? 'Publié' : 'Brouillon' }}</span>
                @if($formation->duration_hours)<span class="text-xs text-slate-400">{{ $formation->duration_hours }}h</span>@endif
            </div>
        </div>

        @if($owned)
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('student.formations.edit', $formation) }}"
               class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-3 py-2 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Modifier
            </a>
            <form method="POST" action="{{ route('student.formations.destroy', $formation) }}" onsubmit="return confirm('Supprimer cette formation et tout son contenu ?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm text-rose-600 hover:text-rose-700 border border-rose-200 rounded-lg px-3 py-2 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Supprimer
                </button>
            </form>
        </div>
        @endif
    </div>

    @if($formation->description)
        <p class="text-sm text-slate-600 mb-8 max-w-3xl">{{ $formation->description }}</p>
    @endif

    {{-- Add chapter (owned only) --}}
    @if($owned)
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900">Contenu</h2>
        <a href="{{ route('student.chapters.create', $formation) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Ajouter un chapitre
        </a>
    </div>
    @endif

    <div class="space-y-4">
        @forelse($formation->chapters as $chapter)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ open: true }">
            <div class="px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0 cursor-pointer flex-1" @click="open = !open">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 font-semibold text-sm flex-shrink-0">{{ $chapter->order }}</div>
                    <div class="min-w-0">
                        <h3 class="font-medium text-slate-900">{{ $chapter->title }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $chapter->subChapters->count() }} sous-chapitre(s)</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($owned)
                    <a href="{{ route('student.chapters.edit', [$formation, $chapter]) }}" class="text-slate-400 hover:text-slate-600 p-1" title="Modifier le chapitre">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </a>
                    <form method="POST" action="{{ route('student.chapters.destroy', [$formation, $chapter]) }}" onsubmit="return confirm('Supprimer ce chapitre ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Supprimer le chapitre">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                    @endif
                    <button type="button" @click="open = !open" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
            </div>

            <div x-show="open" x-transition class="border-t border-slate-100">
                @if($chapter->image_url)
                <div class="px-5 pt-4">
                    <figure>
                        <img src="{{ $chapter->image_url }}" alt="{{ $chapter->image_alt ?? $chapter->title }}" loading="lazy" class="w-full rounded-lg border border-slate-200 object-cover max-h-48">
                        @if($chapter->image_credit)<figcaption class="mt-1.5 text-xs text-slate-400 text-center italic">{{ $chapter->image_credit }}</figcaption>@endif
                    </figure>
                </div>
                @endif

                @php
                    $chapterSources = $chapter->sources;
                    $hasSources = is_array($chapterSources) && count($chapterSources) > 0;
                @endphp
                @if($hasSources)
                <div class="px-5 pt-3 pb-1">
                    <p class="text-xs font-medium text-slate-500 mb-2">Ressources du chapitre</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($chapterSources as $source)
                        @if(isset($source['url']) && isset($source['title']))
                        <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-600 hover:border-brand-300 hover:text-brand-700 transition-colors">
                            <span class="truncate max-w-[150px]">{{ $source['title'] }}</span>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                @foreach($chapter->subChapters as $sub)
                <div class="border-b border-slate-50 last:border-b-0">
                    <div class="px-5 py-3 hover:bg-slate-50 group flex items-center justify-between gap-3">
                        <a href="{{ route('student.formations.subchapter', [$formation, $sub]) }}" class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 text-xs flex-shrink-0">{{ $sub->order }}</div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600 transition-colors truncate">{{ $sub->title }}</p>
                                @php
                                    $subSources = $sub->sources;
                                    $subHasSources = is_array($subSources) && count($subSources) > 0;
                                @endphp
                                @if($sub->image_url || $subHasSources)
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($sub->image_url)
                                        <span class="text-xs text-slate-400">Illustration</span>
                                    @endif
                                    @if($subHasSources)
                                        <span class="text-xs text-slate-400">{{ count($subSources) }} ressource(s)</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </a>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if($owned)
                            <a href="{{ route('student.subchapters.edit', $sub) }}" class="text-slate-400 hover:text-slate-600 p-1" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('student.subchapters.destroy', $sub) }}" onsubmit="return confirm('Supprimer ce sous-chapitre ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-500 p-1" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>

                    @if($sub->quiz && $sub->quiz->status === 'published')
                    <a href="{{ route('student.quizzes.show', $sub->quiz) }}" class="flex items-center gap-3 mx-5 mb-3 px-4 py-3 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 transition-colors group/quiz">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-brand-900">{{ $sub->quiz->title }}</p>
                            <p class="text-xs text-brand-600">{{ $sub->quiz->questions_count ?? $sub->quiz->questions->count() }} questions</p>
                        </div>
                        <span class="text-xs text-brand-600 font-medium group-hover/quiz:text-brand-700">Passer le quiz →</span>
                    </a>
                    @endif
                </div>
                @endforeach

                @if($owned)
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">
                    <a href="{{ route('student.subchapters.create', $chapter) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ajouter un sous-chapitre
                    </a>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Aucun contenu disponible.</p>
            @if($owned)
            <a href="{{ route('student.chapters.create', $formation) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium mt-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Ajouter un chapitre
            </a>
            @endif
        </div>
        @endforelse
    </div>
</div>
@endsection
