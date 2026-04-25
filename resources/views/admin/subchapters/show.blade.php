{{-- resources/views/admin/subchapters/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <a href="{{ route('admin.formations.show', $chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $chapter->formation->name }} → {{ $chapter->title }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $subchapter->title }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="font-semibold text-slate-900 mb-4">Contenu pédagogique</h2>

                @if($subchapter->image_url)
                <figure class="mb-6">
                    <img src="{{ $subchapter->image_url }}" alt="{{ $subchapter->image_alt ?? $subchapter->title }}" loading="lazy" class="w-full rounded-lg border border-slate-200 object-cover max-h-80">
                    @if($subchapter->image_credit)<figcaption class="mt-2 text-xs text-slate-400 text-center italic">{{ $subchapter->image_credit }}</figcaption>@endif
                </figure>
                @endif

                @if($subchapter->mermaid_code)
                <div class="mb-6 bg-slate-50 rounded-lg border border-slate-200 p-4 overflow-x-auto">
                    <p class="text-xs text-slate-500 mb-3 font-medium"><span>📊</span> Diagramme</p>
                    <pre class="mermaid text-sm">{{ $subchapter->mermaid_code }}</pre>
                </div>
                @endif

                @if($subchapter->content)
                    <div class="prose prose-sm prose-slate max-w-none">{!! \App\Services\ContentSanitizer::render($subchapter->content) !!}</div>
                @else
                    <p class="text-sm text-slate-400 italic">Aucun contenu défini.</p>
                @endif

                @if($subchapter->sources && is_array($subchapter->sources) && count($subchapter->sources) > 0)
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3"><span>📚</span> Sources</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($subchapter->sources as $source)
                        <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50 transition-colors group">
                            <span class="text-lg flex-shrink-0">@if(($source['type'] ?? '') === 'wikipedia') 📖 @elseif(($source['type'] ?? '') === 'docs') 📄 @else 🔗 @endif</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-700 truncate">{{ $source['title'] ?? $source['url'] }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ parse_url($source['url'], PHP_URL_HOST) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            {{-- Actions --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.subchapters.edit', [$chapter, $subchapter]) }}" class="block text-sm text-brand-600 hover:text-brand-700">Modifier le contenu</a>
                    @if($subchapter->quiz)
                        <a href="{{ route('admin.quizzes.show', $subchapter->quiz) }}" class="block text-sm text-brand-600 hover:text-brand-700">Voir le quiz ({{ $subchapter->quiz->questions->count() }} questions)</a>
                    @else
                        <a href="{{ route('admin.quizzes.create', $subchapter) }}" class="block text-sm text-brand-600 hover:text-brand-700">Créer un quiz</a>
                    @endif
                </div>
            </div>

            {{-- Flashcards --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Flashcards</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.flashcards.generate', $subchapter) }}">
                        @csrf
                        <button type="submit" class="w-full bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-sm font-medium py-2.5 px-4 rounded-lg transition-colors inline-flex items-center justify-center gap-2"
                            onclick="this.disabled=true; this.innerHTML='Génération...'; this.form.submit();">
                            ⚡ Générer des flashcards
                        </button>
                    </form>
                    <a href="{{ route('admin.flashcards.subchapter', $subchapter) }}" class="block text-center text-sm text-brand-600 hover:text-brand-700 font-medium">
                        Voir les flashcards →
                    </a>
                    <a href="{{ route('admin.flashcards.study', ['sub_chapter_id' => $subchapter->id]) }}" class="block text-center text-sm text-slate-500 hover:text-slate-700">
                        Étudier ce sous-chapitre →
                    </a>
                </div>
            </div>

            {{-- Enrichment metadata --}}
            @if($subchapter->image_url || ($subchapter->sources && count($subchapter->sources) > 0) || $subchapter->mermaid_code)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Enrichissement IA</h3>
                <div class="space-y-2 text-sm text-slate-600">
                    @if($subchapter->image_url)<div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Image</div>@endif
                    @if($subchapter->mermaid_code)<div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Diagramme</div>@endif
                    @if($subchapter->sources && count($subchapter->sources) > 0)<div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> {{ count($subchapter->sources) }} source(s)</div>@endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($subchapter->mermaid_code)
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'strict' });</script>
@endif
@endsection
