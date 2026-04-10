@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Tableau de bord</h1>
        <p class="text-sm text-slate-500 mt-1">Vue d'ensemble de votre plateforme</p>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Apprenants</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $total_students }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Formations</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $total_formations }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">{{ $published_formations }} publiée(s)</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Quiz complétés</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $total_quiz_attempts }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Score moyen</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $avg_quiz_score }}%</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Attempts --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Résultats récents</h2>
            </div>
            @if($recent_attempts->isEmpty())
                <div class="p-8 text-center text-sm text-slate-400">Aucun résultat pour le moment.</div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recent_attempts as $attempt)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $attempt->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $attempt->quiz->title }} — {{ $attempt->completed_at->diffForHumans() }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $attempt->grade_color }}-100 text-{{ $attempt->grade_color }}-800">
                            {{ $attempt->score }}/{{ $attempt->total_questions }} ({{ $attempt->percentage }}%)
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Students --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Derniers inscrits</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">Voir tout →</a>
            </div>
            @if($recent_students->isEmpty())
                <div class="p-8 text-center text-sm text-slate-400">Aucun apprenant inscrit.</div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recent_students as $student)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-xs font-semibold">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $student->name }}</p>
                            <p class="text-xs text-slate-500">{{ $student->email }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
