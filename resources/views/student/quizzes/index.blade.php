@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Mes quiz</h1>
        <p class="text-sm text-slate-500 mt-1">Historique de vos tentatives</p>
    </div>

    @if($attempts->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
            <p class="text-slate-400">Vous n'avez complété aucun quiz pour le moment.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Quiz</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Formation</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Score</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Résultat</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($attempts as $attempt)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $attempt->quiz->title }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $attempt->quiz->subChapter->chapter->formation->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center font-semibold text-{{ $attempt->grade_color }}-600">{{ $attempt->score }}/{{ $attempt->total_questions }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $attempt->grade_color }}-100 text-{{ $attempt->grade_color }}-800">
                                {{ $attempt->percentage }}% — {{ $attempt->grade_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ $attempt->completed_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('student.quizzes.result', $attempt) }}" class="text-brand-600 hover:text-brand-700 text-xs font-medium">Revoir →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $attempts->links() }}</div>
    @endif
</div>
@endsection
