@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Formations</h1>
            <p class="text-sm text-slate-500 mt-1">Gérez vos formations pédagogiques</p>
        </div>
        <a href="{{ route('admin.formations.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvelle formation
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-6">
        <div class="relative max-w-md">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une formation..."
                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
    </form>

    @if($formations->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <p class="text-slate-500 mb-4">Aucune formation créée pour le moment.</p>
            <a href="{{ route('admin.formations.create') }}" class="text-brand-600 hover:text-brand-700 text-sm font-medium">Créer votre première formation →</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($formations as $formation)
            <a href="{{ route('admin.formations.show', $formation) }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700 capitalize">
                        {{ $formation->level }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $formation->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $formation->status === 'published' ? 'Publié' : 'Brouillon' }}
                    </span>
                </div>
                <h3 class="font-semibold text-slate-900 group-hover:text-brand-700 transition-colors">{{ $formation->name }}</h3>
                @if($formation->description)
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ Str::limit($formation->description, 100) }}</p>
                @endif
                <div class="flex items-center gap-4 mt-4 text-xs text-slate-400">
                    <span>{{ $formation->chapters_count }} chapitre(s)</span>
                    <span>{{ $formation->students_count }} apprenant(s)</span>
                    @if($formation->duration_hours)
                        <span>{{ $formation->duration_hours }}h</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $formations->links() }}</div>
    @endif
</div>
@endsection
