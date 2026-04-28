{{-- File: resources/views/student/formations/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Mes formations</h1>
        <p class="text-sm text-slate-500 mt-1">Vos formations suivies et celles que vous avez créées avec l'IA.</p>
    </div>

    @php $userId = auth()->id(); @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($formations as $formation)
        @php $owned = $formation->created_by === $userId; @endphp
        <a href="{{ route('student.formations.show', $formation) }}"
           class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition-all group block">
            <div class="flex items-start justify-between gap-3 mb-3">
                <h3 class="font-medium text-slate-900 group-hover:text-brand-700 transition-colors">{{ $formation->name }}</h3>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-{{ $formation->level_badge_color }}-100 text-{{ $formation->level_badge_color }}-700">{{ ucfirst($formation->level) }}</span>
                @if($owned)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-violet-100 text-violet-700">Créée par moi</span>
                @endif
                @if($formation->status !== 'published')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600">Brouillon</span>
                @endif
            </div>

            @if($formation->description)
                <p class="text-xs text-slate-500 line-clamp-2 mb-3">{{ $formation->description }}</p>
            @endif

            <div class="flex items-center gap-3 text-xs text-slate-400">
                <span>{{ $formation->chapters_count }} chapitre(s)</span>
                @if($formation->duration_hours)<span>{{ $formation->duration_hours }}h</span>@endif
            </div>
        </a>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center">
            <p class="text-sm text-slate-400">Aucune formation pour le moment.</p>
            <p class="text-xs text-slate-400 mt-1">Créez-en une depuis l'onglet IA Playground.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
