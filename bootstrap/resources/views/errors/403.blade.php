@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <p class="text-6xl font-bold text-slate-200">403</p>
        <h1 class="text-xl font-semibold text-slate-900 mt-4">Accès non autorisé</h1>
        <p class="text-sm text-slate-500 mt-2 max-w-md">{{ $exception->getMessage() ?: 'Vous n\'avez pas la permission d\'accéder à cette page.' }}</p>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-700 font-medium mt-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Retour
        </a>
    </div>
</div>
@endsection
