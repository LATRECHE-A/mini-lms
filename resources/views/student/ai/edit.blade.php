@extends('layouts.app')

@section('content')
<div class="fade-in max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('student.ai.show', $generation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>Retour au contenu</a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier le contenu</h1>
        <p class="text-sm text-slate-500 mt-1">Modifiez le contenu avant de le valider.</p>
    </div>

    @if(!$parsed['parsed'])
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
        Ce contenu n'est pas au format structuré et ne peut pas être modifié visuellement. Régénérez-le d'abord.
    </div>
    @else

    <form method="POST" action="{{ route('student.ai.update', $generation) }}" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Chapter Title --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <label for="chapter_title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre du chapitre</label>
            <input type="text" id="chapter_title" name="chapter_title" value="{{ old('chapter_title', $parsed['chapter_title']) }}" required
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-semibold text-lg">
            @error('chapter_title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        {{-- Subchapters --}}
        @if(!empty($parsed['subchapters']))
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Sous-chapitres</h2>
            <div class="space-y-4">
                @foreach($parsed['subchapters'] as $i => $sub)
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center text-brand-700 text-sm font-semibold flex-shrink-0">{{ $i + 1 }}</span>
                        <div class="flex-1">
                            <label class="block text-xs text-slate-500 mb-1">Titre</label>
                            <input type="text" name="subchapters[{{ $i }}][title]" value="{{ old("subchapters.{$i}.title", $sub['title']) }}" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-medium">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Contenu (HTML simple autorisé)</label>
                        <textarea name="subchapters[{{ $i }}][content]" rows="10"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500 leading-relaxed">{{ old("subchapters.{$i}.content", $sub['content']) }}</textarea>
                        @error("subchapters.{$i}.content")<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Quiz --}}
        @if($parsed['quiz'])
        <div>
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Quiz</h2>
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Titre du quiz</label>
                    <input type="text" name="quiz_title" value="{{ old('quiz_title', $parsed['quiz']['title']) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div class="space-y-6">
                    @foreach($parsed['quiz']['questions'] as $qi => $q)
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/50">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 text-xs font-semibold flex-shrink-0 mt-1">{{ $qi + 1 }}</span>
                            <div class="flex-1">
                                <label class="block text-xs text-slate-500 mb-1">Question</label>
                                <input type="text" name="questions[{{ $qi }}][question]" value="{{ old("questions.{$qi}.question", $q['question']) }}" required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                        </div>

                        <div class="ml-10 space-y-2">
                            <p class="text-xs text-slate-500 font-medium">Réponses — sélectionnez la bonne :</p>
                            @foreach($q['options'] as $oi => $option)
                            <div class="flex items-center gap-3">
                                <input type="radio" name="questions[{{ $qi }}][correct_index]" value="{{ $oi }}"
                                    {{ (int)old("questions.{$qi}.correct_index", $q['correct_index']) === $oi ? 'checked' : '' }}
                                    class="text-emerald-600 focus:ring-emerald-500">
                                <input type="text" name="questions[{{ $qi }}][options][]" value="{{ old("questions.{$qi}.options.{$oi}", $option) }}" required
                                    class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors inline-flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Enregistrer
            </button>
            <a href="{{ route('student.ai.show', $generation) }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
        </div>
    </form>
    @endif
</div>
@endsection
