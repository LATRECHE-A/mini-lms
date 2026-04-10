@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Notes</h1>
            <p class="text-sm text-slate-500 mt-1">Gérez les notes des apprenants</p>
        </div>
        <a href="{{ route('admin.notes.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvelle note
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Apprenant</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Formation</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Matière</th>
                    <th class="text-center px-5 py-3 font-medium text-slate-600">Note</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($notes as $note)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $note->user->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $note->formation->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $note->subject }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="font-semibold text-{{ $note->grade_color }}-600">{{ $note->grade }}/20</span>
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $note->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="{{ route('admin.notes.edit', $note) }}" class="text-brand-600 hover:text-brand-700 text-xs">Modifier</a>
                        <form method="POST" action="{{ route('admin.notes.destroy', $note) }}" onsubmit="return confirm('Supprimer ?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-rose-500 hover:text-rose-700 text-xs">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Aucune note enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $notes->links() }}</div>
</div>
@endsection
