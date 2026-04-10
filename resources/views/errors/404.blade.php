@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <p class="text-6xl font-bold text-slate-200">404</p>
        <h1 class="text-xl font-semibold text-slate-900 mt-4">Page introuvable</h1>
        <p class="text-sm text-slate-500 mt-2">La page que vous cherchez n'existe pas ou a été déplacée.</p>
        <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard')) : route('login') }}"
            class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-700 font-medium mt-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Tableau de bord
        </a>
    </div>
</div>
@endsection
