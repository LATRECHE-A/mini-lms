{{-- File: resources/views/student/formations/edit-subchapter.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<div class="fade-in max-w-3xl" x-data="studentEditor()">
    <div class="mb-8">
        <a href="{{ route('student.formations.subchapter', [$formation, $subchapter]) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $formation->name }} → {{ $subchapter->chapter->title }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier « {{ $subchapter->title }} »</h1>
        <p class="text-sm text-slate-500 mt-1">Vous éditez votre propre formation. Les modifications sont enregistrées immédiatement.</p>
    </div>

    <form method="POST" action="{{ route('student.subchapters.update', $subchapter) }}" @submit="beforeSubmit()">
        @csrf @method('PUT')

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $subchapter->title) }}" required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Contenu</label>
                    <div id="quill-editor" class="rounded-lg border border-slate-300" style="min-height: 280px;"></div>
                    <input type="hidden" name="content" x-ref="contentInput">
                    @error('content')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Enregistrer</button>
                <a href="{{ route('student.formations.subchapter', [$formation, $subchapter]) }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </div>
    </form>
</div>

<script>
function studentEditor() {
    return {
        quill: null,
        init() {
            this.$nextTick(() => {
                this.quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [3, 4, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link'],
                            ['clean'],
                        ],
                    },
                    placeholder: 'Rédigez le contenu...',
                });
                const existing = @json(old('content', $subchapter->content ?? ''));
                if (existing) this.quill.root.innerHTML = existing;
            });
        },
        beforeSubmit() {
            if (this.quill) this.$refs.contentInput.value = this.quill.root.innerHTML;
        },
    };
}
</script>
@endsection
