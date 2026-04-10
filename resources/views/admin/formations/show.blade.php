@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div class="min-w-0">
            <a href="{{ route('admin.formations.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Formations
            </a>
            <h1 class="text-2xl font-bold text-slate-900 break-words">{{ $formation->name }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700">{{ ucfirst($formation->level) }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $formation->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $formation->status === 'published' ? 'Publié' : 'Brouillon' }}</span>
                @if($formation->duration_hours)<span class="text-xs text-slate-400">{{ $formation->duration_hours }}h</span>@endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.formations.edit', $formation) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-3 py-2 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.formations.destroy', $formation) }}" onsubmit="return confirm('Supprimer cette formation et tout son contenu ?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center gap-1.5 text-sm text-rose-600 hover:text-rose-700 border border-rose-200 rounded-lg px-3 py-2 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    @if($formation->description)
        <p class="text-sm text-slate-600 mb-8 max-w-3xl">{{ $formation->description }}</p>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Content Tree --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Contenu</h2>
                <a href="{{ route('admin.chapters.create', $formation) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajouter un chapitre
                </a>
            </div>

            @forelse($formation->chapters as $chapter)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ open: true }">
                <div class="px-5 py-4 flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 font-semibold text-sm flex-shrink-0">{{ $chapter->order }}</div>
                        <div class="min-w-0">
                            <h3 class="font-medium text-slate-900">{{ $chapter->title }}</h3>
                            {{-- FIX: replaced orphan green emoji+number with labelled, neutral badges --}}
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-slate-400">{{ $chapter->subChapters->count() }} sous-chapitre(s)</span>
                                @if($chapter->image_url)
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="Illustration disponible">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Illustration
                                    </span>
                                @endif
                                @if($chapter->sources && is_array($chapter->sources) && count($chapter->sources) > 0)
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="{{ count($chapter->sources) }} source(s) de référence">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        {{ count($chapter->sources) }} source(s)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('admin.chapters.edit', [$formation, $chapter]) }}" class="text-slate-400 hover:text-slate-600 p-1" onclick="event.stopPropagation()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.chapters.destroy', [$formation, $chapter]) }}" onsubmit="return confirm('Supprimer ce chapitre ?')" onclick="event.stopPropagation()">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-rose-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </form>
                        <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div x-show="open" x-transition class="border-t border-slate-100">

                    {{-- Chapter image --}}
                    @if($chapter->image_url)
                    <div class="px-5 pt-4">
                        <img src="{{ $chapter->image_url }}" alt="{{ $chapter->image_alt ?? $chapter->title }}" loading="lazy"
                            class="w-full rounded-lg border border-slate-200 object-cover max-h-40">
                        @if($chapter->image_credit)
                        <p class="mt-1 text-xs text-slate-400 text-center italic">{{ $chapter->image_credit }}</p>
                        @endif
                    </div>
                    @endif

                    {{-- Chapter sources --}}
                    @if($chapter->sources && is_array($chapter->sources) && count($chapter->sources) > 0)
                    <div class="px-5 pt-3 flex flex-wrap gap-2">
                        @foreach($chapter->sources as $source)
                            @if(isset($source['url']) && isset($source['title']))
                            <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded text-xs text-slate-600 hover:border-brand-300 hover:text-brand-700 transition-colors">
                                @if(($source['type'] ?? '') === 'wikipedia')
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                @elseif(($source['type'] ?? '') === 'docs')
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @else
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                @endif
                                <span class="truncate max-w-[120px]">{{ $source['title'] }}</span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- Subchapters --}}
                    @foreach($chapter->subChapters as $sub)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                        <a href="{{ route('admin.subchapters.show', [$chapter, $sub]) }}" class="flex items-center gap-3 flex-1 group">
                            <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 text-xs">{{ $sub->order }}</div>
                            <div>
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600">{{ $sub->title }}</p>
                                {{-- FIX: replaced bare emoji+number with clear labelled neutral badges --}}
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    @if($sub->quiz)
                                        <span class="text-xs text-emerald-600 font-medium">Quiz : {{ $sub->quiz->title }}</span>
                                    @endif
                                    @if($sub->image_url)
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="Illustration disponible">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Illustration
                                        </span>
                                    @endif
                                    @if($sub->sources && is_array($sub->sources) && count($sub->sources) > 0)
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="{{ count($sub->sources) }} source(s)">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            {{ count($sub->sources) }} source(s)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if(!$sub->quiz)
                                <a href="{{ route('admin.quizzes.create', $sub) }}" class="text-xs text-brand-600 hover:text-brand-700 px-2 py-1 rounded hover:bg-brand-50">+ Quiz</a>
                            @endif
                            <a href="{{ route('admin.subchapters.edit', [$chapter, $sub]) }}" class="text-slate-400 hover:text-slate-600 p-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach

                    <div class="px-5 py-3">
                        <a href="{{ route('admin.subchapters.create', $chapter) }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajouter un sous-chapitre
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-400">Aucun chapitre. Commencez par en ajouter un.</p>
            </div>
            @endforelse
        </div>

        {{-- Sidebar: Enrolled Students --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-900">Apprenants inscrits ({{ $formation->students->count() }})</h2>
                </div>

                @if($availableStudents->count())
                <form method="POST" action="{{ route('admin.formations.enroll', $formation) }}" class="px-5 py-3 border-b border-slate-100">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select name="user_id" required class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="">Choisir...</option>
                            @foreach($availableStudents as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm px-3 py-2 rounded-lg transition-colors">Inscrire</button>
                    </div>
                </form>
                @endif

                @forelse($formation->students as $student)
                <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-xs font-semibold">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                        <span class="text-sm text-slate-700">{{ $student->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.formations.unenroll', [$formation, $student]) }}" onsubmit="return confirm('Désinscrire cet apprenant ?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-rose-500 hover:text-rose-700">Retirer</button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-sm text-slate-400">Aucun apprenant inscrit.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
