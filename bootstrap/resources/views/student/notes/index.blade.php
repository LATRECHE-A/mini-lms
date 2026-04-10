@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Notes & résultats</h1>
        <p class="text-sm text-slate-500 mt-1">Vos notes et vos annotations personnelles</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Official Grades --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Mes notes officielles</h2>
            </div>
            @forelse($grades as $grade)
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $grade->subject }}</p>
                    <p class="text-xs text-slate-400">{{ $grade->formation->name }}</p>
                    @if($grade->comment)<p class="text-xs text-slate-500 mt-1 italic">{{ $grade->comment }}</p>@endif
                </div>
                <span class="text-lg font-bold text-{{ $grade->grade_color }}-600">{{ $grade->grade }}<span class="text-xs text-slate-400 font-normal">/20</span></span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-slate-400">Aucune note officielle.</div>
            @endforelse
        </div>

        {{-- Personal Notes --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Mes notes personnelles</h2>
            </div>

            <form method="POST" action="{{ route('student.notes.store') }}" class="px-5 py-3 border-b border-slate-100">
                @csrf
                <input type="text" name="title" placeholder="Nouvelle note..." required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm mb-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <div class="flex flex-col sm:flex-row gap-2">
                    <textarea name="content" rows="1" placeholder="Contenu (optionnel)" class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm px-3 py-2 rounded-lg transition-colors">Ajouter</button>
                </div>
            </form>

            @forelse($notes as $note)
            <div class="px-5 py-3 border-b border-slate-50 last:border-b-0">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $note->title }}</p>
                        @if($note->content)<p class="text-xs text-slate-500 mt-1">{{ Str::limit($note->content, 120) }}</p>@endif
                        @if($note->subChapter)<p class="text-xs text-brand-500 mt-1">{{ $note->subChapter->chapter->formation->name ?? '' }} → {{ $note->subChapter->title }}</p>@endif
                        <p class="text-xs text-slate-400 mt-1">{{ $note->updated_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('student.notes.destroy', $note) }}">
                        @csrf @method('DELETE')
                        <button class="text-slate-400 hover:text-rose-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-slate-400">Aucune note personnelle.</div>
            @endforelse
            @if($notes->hasPages())<div class="px-5 py-3">{{ $notes->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
