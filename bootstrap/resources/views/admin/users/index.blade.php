@extends('layouts.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Utilisateurs</h1>
            <p class="text-sm text-slate-500 mt-1">Gérez les comptes de la plateforme</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvel utilisateur
        </a>
    </div>

    <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1 max-w-md">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">
        </div>
        <select name="role" onchange="this.form.submit()" class="px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">
            <option value="">Tous les rôles</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="apprenant" {{ request('role') === 'apprenant' ? 'selected' : '' }}>Apprenant</option>
        </select>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Nom</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Email</th>
                    <th class="text-center px-5 py-3 font-medium text-slate-600">Rôle</th>
                    <th class="text-center px-5 py-3 font-medium text-slate-600">Quiz</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-600">Inscrit le</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-slate-800 hover:text-brand-600">{{ $user->name }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $user->role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-slate-600">{{ $user->completed_quizzes ?? 0 }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-rose-500 hover:text-rose-700 text-xs">Supprimer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
