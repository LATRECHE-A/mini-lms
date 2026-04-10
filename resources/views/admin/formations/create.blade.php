@extends('layouts.app')

@section('content')
<div class="fade-in max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('admin.formations.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Formations
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Nouvelle formation</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.formations.store') }}" class="space-y-5">
            @csrf
            @include('admin.formations._form')
            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Créer la formation</button>
                <a href="{{ route('admin.formations.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
