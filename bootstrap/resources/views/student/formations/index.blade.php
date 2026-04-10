@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Mes formations</h1>
        <p class="text-sm text-slate-500 mt-1">Parcourez vos cours et contenus pédagogiques</p>
    </div>

    @if($formations->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <p class="text-slate-500">Vous n'êtes inscrit à aucune formation pour le moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($formations as $formation)
            <a href="{{ route('student.formations.show', $formation) }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition-all group">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700 mb-3">{{ ucfirst($formation->level) }}</span>
                <h3 class="font-semibold text-slate-900 group-hover:text-brand-700 transition-colors">{{ $formation->name }}</h3>
                @if($formation->description)
                    <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ Str::limit($formation->description, 100) }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-3">{{ $formation->chapters_count }} chapitre(s)</p>
            </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
