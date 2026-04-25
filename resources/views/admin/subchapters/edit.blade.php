{{-- resources/views/admin/subchapters/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<div class="fade-in max-w-3xl" x-data="subchapterEditor()">
    <div class="mb-8">
        <a href="{{ route('admin.formations.show', $chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ $chapter->formation->name }}
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier « {{ $subchapter->title }} »</h1>
    </div>

    <form method="POST" action="{{ route('admin.subchapters.update', [$chapter, $subchapter]) }}" enctype="multipart/form-data" @submit="beforeSubmit()">
        @csrf @method('PUT')

        <div class="space-y-6">
            {{-- Title + Order --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm">Informations générales</h2>
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre <span class="text-rose-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $subchapter->title) }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-slate-700 mb-1.5">Ordre</label>
                    <input type="number" id="order" name="order" value="{{ old('order', $subchapter->order) }}" min="0" class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            {{-- Content — Quill + AI Rewrite --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900 text-sm">Contenu</h2>
                    <button type="button" @click="openRewrite()" :disabled="!hasSelection"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 disabled:opacity-30 disabled:cursor-not-allowed"
                        :class="hasSelection ? 'bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200' : 'bg-slate-50 text-slate-400 border border-slate-200'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        Réécrire avec IA
                    </button>
                </div>
                <p class="text-xs text-slate-400">Sélectionnez du texte puis cliquez « Réécrire avec IA » pour améliorer un passage.</p>

                <div id="quill-editor" class="rounded-lg border border-slate-300" style="min-height: 300px;"></div>
                <input type="hidden" name="content" x-ref="contentInput">
                @error('content')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror

                {{-- AI Rewrite modal --}}
                <div x-show="showRewriteModal" x-transition x-cloak class="fixed inset-0 bg-black/30 z-50 flex items-center justify-center p-4" @click.self="showRewriteModal = false" @keydown.escape.window="showRewriteModal = false">
                    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
                        <h3 class="font-semibold text-slate-900">Réécrire avec IA</h3>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600 max-h-32 overflow-y-auto" x-text="selectedText"></div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Instruction</label>
                            <input type="text" x-model="rewriteInstruction" @keydown.enter.prevent="doRewrite()"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500"
                                placeholder="Ex: Simplifier, ajouter un exemple, reformuler en français plus accessible...">
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="doRewrite()" :disabled="rewriteLoading || !rewriteInstruction.trim()"
                                class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors inline-flex items-center gap-2">
                                <svg :class="rewriteLoading ? '' : 'hidden'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Réécrire
                            </button>
                            <button type="button" @click="showRewriteModal = false" class="text-sm text-slate-500 hover:text-slate-700">Annuler</button>
                        </div>
                        <div :class="rewriteError ? '' : 'hidden'" class="text-sm text-rose-600 bg-rose-50 rounded-lg px-3 py-2" x-text="rewriteError"></div>
                    </div>
                </div>
            </div>

            {{-- Image — upload or URL --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">🖼️ Image illustrative</h2>
                @if($subchapter->image_url)
                <div>
                    <img src="{{ $subchapter->image_url }}" alt="{{ $subchapter->image_alt }}" class="max-h-40 rounded-lg border border-slate-200">
                    @if($subchapter->image_credit)
                    <p class="mt-1 text-xs text-slate-400 italic">{{ $subchapter->image_credit }}</p>
                    @endif
                </div>
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
                        <input type="text" id="image_alt" name="image_alt" value="{{ old('image_alt', $subchapter->image_alt) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label for="image_credit" class="block text-sm font-medium text-slate-700 mb-1.5">Crédit image</label>
                        <input type="text" id="image_credit" name="image_credit" value="{{ old('image_credit', $subchapter->image_credit) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Sources --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📚 Sources</h2>
                <textarea name="sources_json" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder='[{"title":"...","url":"https://...","type":"docs"}]'>{{ old('sources_json', $subchapter->sources ? json_encode($subchapter->sources, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            </div>

            {{-- Mermaid --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📊 Diagramme Mermaid</h2>
                <textarea name="mermaid_code" rows="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="graph TD&#10;    A --> B">{{ old('mermaid_code', $subchapter->mermaid_code) }}</textarea>
                @if($subchapter->mermaid_code)
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <pre class="mermaid text-sm">{{ $subchapter->mermaid_code }}</pre>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Enregistrer</button>
                <a href="{{ route('admin.formations.show', $chapter->formation) }}" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
            </div>
        </div>
    </form>
</div>

@if($subchapter->mermaid_code)
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'strict' });</script>
@endif

<script>
function subchapterEditor() {
    return {
        quill: null,
        imageUrl: '{{ old('image_url', $subchapter->image_url ?? '') }}',
        uploadStatus: '', uploadError: false,
        hasSelection: false, selectedText: '', selectedRange: null,
        showRewriteModal: false, rewriteInstruction: '', rewriteLoading: false, rewriteError: '',

        init() {
            this.$nextTick(() => {
                this.quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: { toolbar: [[{header:[3,4,false]}],['bold','italic','underline','code-block'],[{list:'ordered'},{list:'bullet'}],['link'],['clean']] },
                    placeholder: 'Rédigez le contenu...',
                });
                const existing = @json(old('content', $subchapter->content ?? ''));
                if (existing) this.quill.root.innerHTML = existing;
                this.quill.on('selection-change', (range) => {
                    if (range && range.length > 0) {
                        this.hasSelection = true;
                        this.selectedText = this.quill.getText(range.index, range.length).trim();
                        this.selectedRange = range;
                    } else {
                        this.hasSelection = false;
                    }
                });
            });
        },

        beforeSubmit() { if (this.quill) this.$refs.contentInput.value = this.quill.root.innerHTML; },

        openRewrite() {
            if (!this.hasSelection || !this.selectedText) return;
            this.rewriteInstruction = ''; this.rewriteError = ''; this.showRewriteModal = true;
        },

        async doRewrite() {
            if (this.rewriteLoading || !this.rewriteInstruction.trim()) return;
            this.rewriteLoading = true; this.rewriteError = '';
            try {
                const resp = await fetch('{{ route("admin.ai.rewrite") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ selected_text: this.selectedText, instruction: this.rewriteInstruction }),
                });
                const data = await resp.json();
                if (!resp.ok) { this.rewriteError = data.error || data.message || 'Erreur ' + resp.status; return; }
                if (data.error) { this.rewriteError = data.error; return; }
                if (this.selectedRange && data.rewritten) {
                    this.quill.deleteText(this.selectedRange.index, this.selectedRange.length);
                    this.quill.clipboard.dangerouslyPasteHTML(this.selectedRange.index, data.rewritten);
                    this.showRewriteModal = false;
                }
            } catch (e) {
                this.rewriteError = 'Erreur de connexion. Vérifiez votre réseau et réessayez.';
            } finally { this.rewriteLoading = false; }
        },

        async uploadImage(event) {
            const file = event.target.files[0]; if (!file) return;
            if (file.size > 5*1024*1024) { this.uploadStatus = 'Image trop grande (max 5 Mo).'; this.uploadError = true; return; }
            this.uploadStatus = 'Envoi en cours...'; this.uploadError = false;
            const fd = new FormData(); fd.append('image', file);
            try {
                const r = await fetch('{{ route("admin.upload.image") }}', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }, body: fd,
                });
                const d = await r.json();
                if (!r.ok) { this.uploadStatus = d.message || d.errors?.image?.[0] || 'Erreur.'; this.uploadError = true; return; }
                this.imageUrl = d.url; this.uploadStatus = 'Image uploadée.'; this.uploadError = false;
            } catch(e) { this.uploadStatus = 'Erreur réseau.'; this.uploadError = true; }
        },
    };
}
</script>
@endsection
