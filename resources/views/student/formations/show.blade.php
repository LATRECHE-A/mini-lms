@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <a href="{{ route('student.formations.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>Mes formations</a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $formation->name }}</h1>
        <div class="flex items-center gap-3 mt-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700">{{ ucfirst($formation->level) }}</span>
            @if($formation->duration_hours)<span class="text-xs text-slate-400">{{ $formation->duration_hours }}h</span>@endif
        </div>
        @if($formation->description)
            <p class="text-sm text-slate-600 mt-3 max-w-3xl">{{ $formation->description }}</p>
        @endif
    </div>

    <div class="space-y-6">
        @forelse($formation->chapters as $chapter)
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ open: true }">

            {{-- Chapter header --}}
            <div class="px-5 py-4 flex items-center justify-between cursor-pointer" @click="open = !open">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 font-semibold text-sm flex-shrink-0">{{ $chapter->order }}</div>
                    <div class="min-w-0">
                        <h3 class="font-medium text-slate-900">{{ $chapter->title }}</h3>
                        {{-- FIX: clean subchapter count label only, no orphan green number --}}
                        <p class="text-xs text-slate-400 mt-0.5">{{ $chapter->subChapters->count() }} sous-chapitre(s)</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

            <div x-show="open" x-transition class="border-t border-slate-100">

                {{-- Chapter image --}}
                @if($chapter->image_url)
                <div class="px-5 pt-4">
                    <figure>
                        <img src="{{ $chapter->image_url }}" alt="{{ $chapter->image_alt ?? $chapter->title }}" loading="lazy"
                            class="w-full rounded-lg border border-slate-200 object-cover max-h-48">
                        @if($chapter->image_credit)
                        <figcaption class="mt-1.5 text-xs text-slate-400 text-center italic">{{ $chapter->image_credit }}</figcaption>
                        @endif
                    </figure>
                </div>
                @endif

                {{-- Chapter sources --}}
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
                                @if(($source['type'] ?? '') === 'wikipedia')
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                @elseif(($source['type'] ?? '') === 'docs')
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @else
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                @endif
                                <span class="truncate max-w-[150px]">{{ $source['title'] }}</span>
                                <svg class="w-3 h-3 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Subchapters --}}
                @foreach($chapter->subChapters as $sub)
                <div class="border-b border-slate-50 last:border-b-0">
                    <a href="{{ route('student.formations.subchapter', [$formation, $sub]) }}" class="block px-5 py-3 hover:bg-slate-50 group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 text-xs">{{ $sub->order }}</div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600 transition-colors">{{ $sub->title }}</p>
                                    {{-- FIX: show clean pill badges, no orphan numbers --}}
                                    @php
                                        $subSources = $sub->sources;
                                        $subHasSources = is_array($subSources) && count($subSources) > 0;
                                    @endphp
                                    @if($sub->image_url || $subHasSources)
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($sub->image_url)
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Illustration
                                            </span>
                                        @endif
                                        @if($subHasSources)
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                {{ count($subSources) }} ressource(s)
                                            </span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </a>

                    {{-- Quiz link --}}
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
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Aucun contenu disponible.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
