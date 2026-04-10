@extends('layouts.app')

@section('content')
<div class="fade-in max-w-2xl">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Mes tâches</h1>
        <p class="text-sm text-slate-500 mt-1">Organisez votre apprentissage</p>
    </div>

    {{-- Add todo --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <form method="POST" action="{{ route('student.todos.store') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Nouvelle tâche</label>
                <input type="text" id="title" name="title" required placeholder="Ex: Réviser le chapitre 3..."
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label for="due_date" class="block text-sm font-medium text-slate-700 mb-1.5">Échéance</label>
                <input type="date" id="due_date" name="due_date" min="{{ date('Y-m-d') }}"
                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors whitespace-nowrap">
                Ajouter
            </button>
        </form>
    </div>

    {{-- Todo list --}}
    <div class="bg-white rounded-xl border border-slate-200">
        @forelse($todos as $todo)
        <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100 last:border-b-0 {{ $todo->is_completed ? 'opacity-50' : '' }}">
            <div class="flex items-center gap-3 flex-1">
                <form method="POST" action="{{ route('student.todos.toggle', $todo) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors {{ $todo->is_completed ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-brand-500' }}">
                        @if($todo->is_completed)
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        @endif
                    </button>
                </form>
                <div>
                    <p class="text-sm {{ $todo->is_completed ? 'line-through text-slate-400' : 'text-slate-800' }}">{{ $todo->title }}</p>
                    @if($todo->due_date)
                        <p class="text-xs {{ $todo->is_overdue ? 'text-rose-600 font-medium' : 'text-slate-400' }}">
                            {{ $todo->is_overdue ? 'En retard — ' : '' }}{{ $todo->due_date->format('d/m/Y') }}
                        </p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('student.todos.destroy', $todo) }}">
                @csrf @method('DELETE')
                <button class="text-slate-400 hover:text-rose-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-slate-400">
            Aucune tâche. Ajoutez-en une ci-dessus !
        </div>
        @endforelse
    </div>
</div>
@endsection
