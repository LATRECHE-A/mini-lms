{{-- resources/views/admin/chapters/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<div class="fade-in max-w-3xl" x-data="chapterEditor()">
    <div class="mb-8">
        <a href="{{ route('admin.formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>{{ $formation->name }}</a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier « {{ $chapter->title }} »</h1>
    </div>

    <form method="POST" action="{{ route('admin.chapters.update', [$formation, $chapter]) }}" enctype="multipart/form-data" @submit="beforeSubmit()">
        @csrf @method('PUT')

        <div class="space-y-6">
            {{-- Title + Description + Order --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm">Informations générales</h2>
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $chapter->title) }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                    <div id="quill-description" class="rounded-lg border border-slate-300" style="min-height: 120px;"></div>
                    <input type="hidden" name="description" x-ref="descInput">
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-slate-700 mb-1.5">Ordre</label>
                    <input type="number" id="order" name="order" value="{{ old('order', $chapter->order) }}" min="0" class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            {{-- Image --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">🖼️ Image du chapitre</h2>
                @if($chapter->image_url)
                <img src="{{ $chapter->image_url }}" alt="{{ $chapter->image_alt }}" class="max-h-40 rounded-lg border border-slate-200">
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Uploader une image</label>
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" @change="uploadImage($event)"
                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p :class="uploadStatus ? '' : 'hidden'" class="mt-1 text-xs" :class="uploadError ? 'text-rose-600' : 'text-emerald-600'" x-text="uploadStatus"></p>
                </div>
                <div class="text-xs text-slate-400 text-center">— ou —</div>
                <div>
                    <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1.5">URL de l'image</label>
                    <input type="url" id="image_url" name="image_url" x-model="imageUrl" placeholder="https://..." class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="image_alt" class="block text-sm font-medium text-slate-700 mb-1.5">Texte alternatif</label>
                        <input type="text" id="image_alt" name="image_alt" value="{{ old('image_alt', $chapter->image_alt) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label for="image_credit" class="block text-sm font-medium text-slate-700 mb-1.5">Crédit</label>
                        <input type="text" id="image_credit" name="image_credit" value="{{ old('image_credit', $chapter->image_credit) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Sources --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📚 Sources</h2>
                <textarea name="sources_json" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder='[{"title":"...","url":"https://...","type":"docs"}]'>{{ old('sources_json', $chapter->sources ? json_encode($chapter->sources, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            </div>

            {{-- Mermaid --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📊 Diagramme Mermaid</h2>
                <textarea name="mermaid_code" rows="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="graph TD&#10;    A --> B">{{ old('mermaid_code', $chapter->mermaid_code) }}</textarea>
                @if($chapter->mermaid_code)
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <pre class="mermaid text-sm">{{ $chapter->mermaid_code }}</pre>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Enregistrer</button>
                <a href="{{ route('admin.formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </div>
    </form>
</div>

@if($chapter->mermaid_code)
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'strict' });</script>
@endif

<script>
function chapterEditor() {
    return {
        quill: null,
        imageUrl: '{{ old('image_url', $chapter->image_url) }}',
        uploadStatus: '', uploadError: false,

        init() {
            this.$nextTick(() => {
                this.quill = new Quill('#quill-description', {
                    theme: 'snow',
                    modules: { toolbar: [['bold','italic'],[{list:'bullet'}],['link'],['clean']] },
                    placeholder: 'Description du chapitre...',
                });
                const existing = @json(old('description', $chapter->description ?? ''));
                if (existing) this.quill.root.innerHTML = existing;
            });
        },

        beforeSubmit() {
            if (this.quill) this.$refs.descInput.value = this.quill.root.innerHTML;
        },

        async uploadImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 5*1024*1024) { this.uploadStatus = 'Max 5 Mo.'; this.uploadError = true; return; }
            this.uploadStatus = 'Envoi...'; this.uploadError = false;
            const fd = new FormData(); fd.append('image', file);
            try {
                const r = await fetch('{{ route("admin.upload.image") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: fd,
                });
                const d = await r.json();
                if (!r.ok) { this.uploadStatus = d.message || 'Erreur.'; this.uploadError = true; return; }
                this.imageUrl = d.url; this.uploadStatus = 'Uploadée.'; this.uploadError = false;
            } catch(e) { this.uploadStatus = 'Erreur réseau.'; this.uploadError = true; }
        },
    };
}
</script>
@endsection
