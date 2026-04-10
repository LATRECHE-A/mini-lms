@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quiz</h1>
            <p class="text-sm text-slate-500 mt-1">Tous les quiz de la plateforme</p>
        </div>
    </div>

    @if($quizzes->isEmpty() && !request('search'))
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
            <p class="text-slate-400">Aucun quiz créé. Ajoutez des quiz depuis vos formations.</p>
        </div>
    @else
        {{-- Search --}}
        <form method="GET" class="mb-6">
            <div class="relative max-w-md">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un quiz..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
        </form>
        <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Quiz</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Formation</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Questions</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Tentatives</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($quizzes as $quiz)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.quizzes.show', $quiz) }}" class="font-medium text-slate-800 hover:text-brand-600">{{ $quiz->title }}</a>
                            <p class="text-xs text-slate-400">{{ $quiz->subChapter->title }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $quiz->subChapter->chapter->formation->name }}</td>
                        <td class="px-5 py-3 text-center">{{ $quiz->questions_count }}</td>
                        <td class="px-5 py-3 text-center">{{ $quiz->attempts_count }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $quiz->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $quiz->status === 'published' ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.quizzes.show', $quiz) }}" class="text-brand-600 hover:text-brand-700 text-xs font-medium">Détails →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $quizzes->links() }}</div>
    @endif
</div>
@endsection
