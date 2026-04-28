{{-- File: resources/views/student/chapters/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('student.formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $formation->name }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier « {{ $chapter->title }} »</h1>
    </div>

    <form method="POST" action="{{ route('student.chapters.update', [$formation, $chapter]) }}">
        @csrf @method('PUT')

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm">Informations générales</h2>
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $chapter->title) }}" required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('title') border-rose-400 @enderror">
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('description', $chapter->description) }}</textarea>
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-slate-700 mb-1.5">Ordre</label>
                    <input type="number" id="order" name="order" value="{{ old('order', $chapter->order) }}" min="0"
                           class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">🖼️ Image du chapitre</h2>
                @if($chapter->image_url)
                    <img src="{{ $chapter->image_url }}" alt="{{ $chapter->image_alt }}" class="max-h-40 rounded-lg border border-slate-200">
                @endif
                <div>
                    <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1.5">URL de l'image</label>
                    <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $chapter->image_url) }}" placeholder="https://..."
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    @error('image_url')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="image_alt" class="block text-sm font-medium text-slate-700 mb-1.5">Texte alternatif</label>
                        <input type="text" id="image_alt" name="image_alt" value="{{ old('image_alt', $chapter->image_alt) }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label for="image_credit" class="block text-sm font-medium text-slate-700 mb-1.5">Crédit</label>
                        <input type="text" id="image_credit" name="image_credit" value="{{ old('image_credit', $chapter->image_credit) }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📚 Sources (JSON)</h2>
                <textarea name="sources_json" rows="4"
                          class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          placeholder='[{"title":"...","url":"https://...","type":"docs"}]'>{{ old('sources_json', $chapter->sources ? json_encode($chapter->sources, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📊 Diagramme Mermaid</h2>
                <textarea name="mermaid_code" rows="6"
                          class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                          placeholder="graph TD&#10;    A --> B">{{ old('mermaid_code', $chapter->mermaid_code) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Enregistrer</button>
                <a href="{{ route('student.formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </div>
    </form>
</div>
@endsection
