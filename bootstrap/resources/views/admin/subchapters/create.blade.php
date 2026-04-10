@extends('layouts.app')

@section('content')
<div class="fade-in max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('admin.formations.show', $chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $chapter->formation->name }} → {{ $chapter->title }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Nouveau sous-chapitre</h1>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.subchapters.store', $chapter) }}" class="space-y-5">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 @error('title') border-rose-400 @enderror">
                @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-slate-700 mb-1.5">Contenu (HTML autorisé)</label>
                <textarea id="content" name="content" rows="12" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('content') }}</textarea>
                <p class="mt-1 text-xs text-slate-400">Vous pouvez utiliser du HTML simple: &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;</p>
            </div>
            <div>
                <label for="order" class="block text-sm font-medium text-slate-700 mb-1.5">Ordre</label>
                <input type="number" id="order" name="order" value="{{ old('order', $nextOrder ?? 1) }}" min="0" class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Ajouter</button>
                <a href="{{ route('admin.formations.show', $chapter->formation) }}" class="text-sm text-slate-500">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
