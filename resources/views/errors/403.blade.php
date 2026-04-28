{{-- File: resources/views/errors/403.blade.php --}}
@extends('layouts.error', ['title' => 'Accès refusé'])

@section('content')
    <p class="text-7xl font-bold text-brand-200 leading-none">403</p>
    <h1 class="text-2xl font-semibold text-slate-900 mt-4">Accès non autorisé</h1>
    <p class="text-sm text-slate-500 mt-3">
        {{ ($exception ?? null) && $exception->getMessage()
            ? $exception->getMessage()
            : "Vous n'avez pas la permission d'accéder à cette page." }}
    </p>

    <div class="mt-8 flex items-center justify-center gap-3">
        @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}"
               class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors">
                Retour au tableau de bord
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 py-2.5 px-3">
                    Se déconnecter
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors">
                Se connecter
            </a>
        @endauth
    </div>
@endsection
