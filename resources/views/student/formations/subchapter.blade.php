{{-- resources/views/student/formations/subchapter.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <a href="{{ route('student.formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $formation->name }} → {{ $subchapter->chapter->title }}
        </a>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">{{ $subchapter->title }}</h1>
            {{-- Edit button only for formations created by this student --}}
            @if($formation->isOwnedBy(auth()->user()))
            <a href="{{ route('student.subchapters.edit', $subchapter) }}" class="text-sm text-slate-500 hover:text-brand-600 inline-flex items-center gap-1.5 border border-slate-200 rounded-lg px-3 py-1.5 hover:border-brand-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Modifier
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                @if($subchapter->image_url)
                <figure class="mb-6">
                    <img src="{{ $subchapter->image_url }}" alt="{{ $subchapter->image_alt ?? $subchapter->title }}" loading="lazy" class="w-full rounded-lg border border-slate-200 object-cover max-h-80">
                    @if($subchapter->image_credit)<figcaption class="mt-2 text-xs text-slate-400 text-center italic">{{ $subchapter->image_credit }}</figcaption>@endif
                </figure>
                @endif

                @if($subchapter->mermaid_code)
                <div class="mb-6 bg-gradient-to-br from-slate-50 to-sky-50 rounded-lg border border-slate-200 p-5 overflow-x-auto">
                    <p class="text-xs text-slate-500 mb-3 font-medium">📊 Schéma explicatif</p>
                    <div class="flex justify-center"><pre class="mermaid text-sm">{{ $subchapter->mermaid_code }}</pre></div>
                </div>
                @endif

                @if($subchapter->content)
                    <div class="prose prose-sm prose-slate max-w-none">{!! \App\Services\ContentSanitizer::render($subchapter->content) !!}</div>
                @else
                    <p class="text-sm text-slate-400 italic">Contenu à venir.</p>
                @endif

                @if($subchapter->sources && is_array($subchapter->sources) && count($subchapter->sources) > 0)
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">📚 Pour aller plus loin</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($subchapter->sources as $source)
                        <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50 transition-colors group">
                            <span class="text-lg">@if(($source['type'] ?? '') === 'wikipedia') 📖 @elseif(($source['type'] ?? '') === 'docs') 📄 @else 🔗 @endif</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-700 truncate">{{ $source['title'] ?? 'Lien' }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ parse_url($source['url'], PHP_URL_HOST) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                {{-- Flashcards --}}
                <form method="POST" action="{{ route('student.flashcards.generate', $subchapter) }}">
                    @csrf
                    <button type="submit" class="bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-700 text-sm font-medium py-2.5 px-5 rounded-lg transition-colors inline-flex items-center gap-2"
                        onclick="this.disabled=true; this.innerHTML='⚡ Génération...'; this.form.submit();">
                        ⚡ Flashcards IA
                    </button>
                </form>

                {{-- AI Quiz (only if no quiz exists) --}}
                @if(!$subchapter->quiz)
                <form method="POST" action="{{ route('student.ai.generate-quiz', $subchapter) }}">
                    @csrf
                    <button type="submit" class="bg-violet-50 hover:bg-violet-100 border border-violet-200 text-violet-700 text-sm font-medium py-2.5 px-5 rounded-lg transition-colors inline-flex items-center gap-2"
                        onclick="this.disabled=true; this.innerHTML='🧠 Génération...'; this.form.submit();">
                        🧠 Quiz IA
                    </button>
                </form>
                @endif

                <a href="{{ route('student.flashcards.subchapter', $subchapter) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Mes flashcards →</a>
                <a href="{{ route('student.flashcards.study', ['sub_chapter_id' => $subchapter->id]) }}" class="text-sm text-slate-500 hover:text-slate-700">Étudier →</a>
            </div>

            @if($subchapter->quiz && $subchapter->quiz->status === 'published')
            <a href="{{ route('student.quizzes.show', $subchapter->quiz) }}" class="block bg-brand-50 border border-brand-200 rounded-xl p-5 hover:bg-brand-100 transition-colors group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-brand-900">{{ $subchapter->quiz->title }}</p>
                            <p class="text-xs text-brand-600">{{ $subchapter->quiz->questions->count() }} questions</p>
                        </div>
                    </div>
                    <span class="text-sm text-brand-600 font-medium group-hover:text-brand-700">Commencer →</span>
                </div>
            </a>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="bg-white rounded-xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-900">Mes notes personnelles</h2>
                </div>
                <form method="POST" action="{{ route('student.notes.store') }}" class="px-5 py-3 border-b border-slate-100">
                    @csrf
                    <input type="hidden" name="sub_chapter_id" value="{{ $subchapter->id }}">
                    <input type="text" name="title" placeholder="Titre..." required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm mb-2 focus:ring-2 focus:ring-brand-500">
                    <textarea name="content" rows="2" placeholder="Contenu..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm mb-2 focus:ring-2 focus:ring-brand-500"></textarea>
                    <button type="submit" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Enregistrer</button>
                </form>
                @forelse($personalNotes as $note)
                <div class="px-5 py-3 border-b border-slate-50 last:border-b-0">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $note->title }}</p>
                            @if($note->content)<p class="text-xs text-slate-500 mt-1">{{ Str::limit($note->content, 100) }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('student.notes.destroy', $note) }}">@csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-rose-600 p-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-4 text-center text-xs text-slate-400">Aucune note.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($subchapter->mermaid_code)
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'strict' });</script>
@endif
@endsection
