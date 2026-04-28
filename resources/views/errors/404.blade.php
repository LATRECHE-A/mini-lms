{{-- File: resources/views/errors/404.blade.php --}}
@extends('layouts.error', ['title' => 'Page introuvable'])

@section('content')
    <p class="text-7xl font-bold text-brand-200 leading-none">404</p>
    <h1 class="text-2xl font-semibold text-slate-900 mt-4">Page introuvable</h1>
    <p class="text-sm text-slate-500 mt-3">
        La ressource demandée n'existe pas ou a été supprimée.
    </p>

    <div class="mt-8 flex items-center justify-center gap-3">
        @auth
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}"
               class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors">
                Retour au tableau de bord
            </a>
        @else
            <a href="{{ route('login') }}"
               class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors">
                Se connecter
            </a>
        @endauth
    </div>
@endsection
