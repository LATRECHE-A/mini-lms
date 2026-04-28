{{-- File: resources/views/errors/500.blade.php --}}
@extends('layouts.error', ['title' => 'Erreur serveur'])

@section('content')
    <p class="text-7xl font-bold text-rose-200 leading-none">500</p>
    <h1 class="text-2xl font-semibold text-slate-900 mt-4">Une erreur est survenue</h1>
    <p class="text-sm text-slate-500 mt-3">
        Le serveur a rencontré une erreur inattendue. Veuillez réessayer dans un instant.
    </p>

    <div class="mt-8 flex items-center justify-center gap-3">
        <a href="{{ url('/') }}"
           class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition-colors">
            Retour à l'accueil
        </a>
    </div>
@endsection
