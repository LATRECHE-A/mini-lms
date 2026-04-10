@extends('layouts.app')

@section('content')
<div class="fade-in max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.formations.show', $subchapter->chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $subchapter->chapter->formation->name }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Créer un quiz</h1>
        <p class="text-sm text-slate-500 mt-1">Pour : {{ $subchapter->title }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.quizzes.store', $subchapter) }}" class="space-y-5">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre du quiz *</label>
                <input type="text" id="title" name="title" value="{{ old('title', 'Quiz — '.$subchapter->title) }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">Statut</label>
                <select id="status" name="status" class="w-48 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="draft">Brouillon</option>
                    <option value="published">Publié</option>
                </select>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Créer le quiz</button>
                <a href="{{ route('admin.formations.show', $subchapter->chapter->formation) }}" class="text-sm text-slate-500">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
