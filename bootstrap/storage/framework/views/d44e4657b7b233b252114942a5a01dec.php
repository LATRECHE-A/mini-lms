<?php $__env->startSection('content'); ?>
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; appearance: textfield; }
    @keyframes mic-pulse { 0% { transform: scale(0.9); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }
    .mic-ring { animation: mic-pulse 1.2s ease-out infinite; }
</style>

<div class="fade-in max-w-3xl" x-data="aiPlayground()">
    <div class="mb-8">
        <a href="<?php echo e(route('admin.ai.index')); ?>" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>IA Playground</a>
        <h1 class="text-2xl font-bold text-slate-900">Nouvelle génération IA</h1>
        <p class="text-sm text-slate-500 mt-1">Décrivez le contenu pédagogique que vous souhaitez générer.</p>
    </div>

    <?php if(!$isAvailable): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6 text-sm">
        Service IA non configuré. Ajoutez <code class="bg-amber-100 px-1 rounded">GEMINI_API_KEY</code> dans .env.
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.ai.generate')); ?>" enctype="multipart/form-data"
        @submit.prevent="submitForm($event)">
        <?php echo csrf_field(); ?>

        <div :class="loading ? 'opacity-50 pointer-events-none select-none' : ''" class="transition-opacity duration-300 space-y-6">

            
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <label for="prompt" class="block text-sm font-medium text-slate-700 mb-2">Sujet / Instructions <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <textarea id="prompt" name="prompt" rows="5" required minlength="10" maxlength="5000"
                        x-ref="promptArea"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-y"
                        :class="micAvailable ? 'pr-12' : ''"
                        placeholder="Ex: Créez un cours complet sur les bases de Python pour débutants..."><?php echo e(old('prompt')); ?></textarea>

                    <button type="button"
                        :class="micAvailable ? '' : 'hidden'"
                        @click.stop="toggleVoice()"
                        :aria-label="isListening ? 'Arrêter la saisie vocale' : 'Démarrer la saisie vocale'"
                        class="absolute right-3 top-3 w-9 h-9 rounded-full flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 z-10"
                        :style="isListening ? 'background:#fee2e2;color:#dc2626' : 'background:#f1f5f9;color:#64748b'">
                        <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                        <span :class="isListening ? 'mic-ring absolute inset-0 rounded-full border-2 border-rose-400 pointer-events-none' : 'hidden'"></span>
                    </button>
                </div>

                <div :class="interimTranscript ? '' : 'hidden'" class="mt-2 px-3 py-2 bg-sky-50 border border-sky-200 rounded-lg text-sm text-sky-700 italic" x-text="'🎤 ' + interimTranscript"></div>

                
                <div :class="micAvailable ? 'flex' : 'hidden'" class="items-center gap-2 mt-2">
                    <label class="text-xs text-slate-400">Langue vocale :</label>
                    <select x-model="voiceLang" @change="saveVoiceLang()" class="text-xs border border-slate-200 rounded px-2 py-1 text-slate-600 bg-white">
                        <option value="fr-FR">Français</option>
                        <option value="en-US">English</option>
                        <option value="es-ES">Español</option>
                        <option value="de-DE">Deutsch</option>
                    </select>
                </div>

                <div :class="micUnavailableReason ? '' : 'hidden'" class="mt-2 text-xs text-slate-400 italic" x-text="micUnavailableReason"></div>
                <div :class="voiceError ? '' : 'hidden'" class="mt-2 text-xs text-rose-600" x-text="voiceError"></div>

                <?php $__errorArgs = ['prompt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 mb-1.5">Type de contenu</label>
                        <select id="type" name="type" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="mixed" <?php echo e(old('type', 'mixed') === 'mixed' ? 'selected' : ''); ?>>Cours + Quiz</option>
                            <option value="course" <?php echo e(old('type') === 'course' ? 'selected' : ''); ?>>Cours uniquement</option>
                            <option value="quiz" <?php echo e(old('type') === 'quiz' ? 'selected' : ''); ?>>Quiz uniquement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de chapitres</label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click.prevent="chapterCount = Math.max(1, chapterCount - 1)" class="w-9 h-9 rounded-lg border border-slate-300 flex items-center justify-center text-slate-600 hover:bg-slate-50 text-lg font-medium select-none">−</button>
                            <input type="number" name="chapter_count" x-model.number="chapterCount" min="1" max="10" readonly
                                class="w-16 text-center px-2 py-2 rounded-lg border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500">
                            <button type="button" @click.prevent="chapterCount = Math.min(10, chapterCount + 1)" class="w-9 h-9 rounded-lg border border-slate-300 flex items-center justify-center text-slate-600 hover:bg-slate-50 text-lg font-medium select-none">+</button>
                        </div>
                        <?php $__errorArgs = ['chapter_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Niveau de détail</label>
                        <div class="flex rounded-lg border border-slate-300 overflow-hidden">
                            <label class="flex-1 text-center cursor-pointer transition-colors text-xs py-2.5" :class="depth === 'standard' ? 'bg-brand-600 text-white font-medium' : 'bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="depth" value="standard" x-model="depth" class="sr-only"> Standard
                            </label>
                            <label class="flex-1 text-center cursor-pointer transition-colors text-xs py-2.5 border-l border-r border-slate-300" :class="depth === 'detailed' ? 'bg-brand-600 text-white font-medium' : 'bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="depth" value="detailed" x-model="depth" class="sr-only"> Détaillé
                            </label>
                            <label class="flex-1 text-center cursor-pointer transition-colors text-xs py-2.5" :class="depth === 'exhaustive' ? 'bg-brand-600 text-white font-medium' : 'bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="depth" value="exhaustive" x-model="depth" class="sr-only"> Exhaustif
                            </label>
                        </div>
                        <?php $__errorArgs = ['depth'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Fichiers joints <span class="text-slate-400 font-normal">(optionnel)</span></label>
                <p class="text-xs text-slate-400 mb-3">PDF, images, DOCX, PPTX — max 5 fichiers, 10 Mo chacun, 25 Mo au total.</p>
                <div class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
                    :class="dragOver ? 'border-brand-500 bg-brand-50' : 'border-slate-300 hover:border-brand-400'"
                    @click="$refs.fileInput.click()" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p class="text-sm text-slate-500">Glissez ou <span class="text-brand-600 font-medium">cliquez</span></p>
                </div>
                <input type="file" name="attachments[]" multiple x-ref="fileInput" @change="handleFiles($event)" class="hidden" accept=".pdf,.txt,.md,.jpg,.jpeg,.png,.webp,.gif,.docx,.pptx">
                <div :class="files.length > 0 ? '' : 'hidden'" class="mt-3 space-y-2">
                    <template x-for="(f, i) in files" :key="i">
                        <div class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-sm text-slate-700 truncate" x-text="f.name"></span>
                                <span class="text-xs text-slate-400 flex-shrink-0" x-text="formatSize(f.size)"></span>
                            </div>
                            <button type="button" @click.stop="removeFile(i)" class="text-slate-400 hover:text-rose-500 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                    </template>
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-400" x-text="files.length + '/5'"></span>
                        <span :class="totalSize > 25*1024*1024 ? 'text-rose-600 font-medium' : 'text-slate-400'" x-text="'Total : ' + formatSize(totalSize)"></span>
                    </div>
                    <p :class="fileError ? '' : 'hidden'" class="text-xs text-rose-600" x-text="fileError"></p>
                </div>
                <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" :disabled="loading || !<?php echo e($isAvailable ? 'true' : 'false'); ?>"
                class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium py-3 px-8 rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                <svg :class="loading ? 'hidden' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <svg :class="loading ? '' : 'hidden'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="loading ? 'Génération en cours... (10–60s)' : 'Générer le contenu'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function aiPlayground() {
    return {
        chapterCount: <?php echo e((int) old('chapter_count', 3)); ?>,
        depth: '<?php echo e(old('depth', 'standard')); ?>',
        files: [], totalSize: 0, fileError: '', dragOver: false,
        micAvailable: false,
        micUnavailableReason: '',
        recognition: null,
        isListening: false,
        interimTranscript: '',
        voiceLang: 'fr-FR',
        voiceError: '',
        loading: false,

        init() {
            try { this.voiceLang = localStorage.getItem('voiceLang') || 'fr-FR'; } catch(e) {}

            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

            if (!SR) {
                this.micUnavailableReason = 'Reconnaissance vocale non supportée (utilisez Chrome ou Edge).';
                return;
            }
            if (!isSecure) {
                this.micUnavailableReason = 'La saisie vocale nécessite HTTPS. Accédez au site via https://.';
                return;
            }

            this.micAvailable = true;
            const rec = new SR();
            rec.continuous = false;
            rec.interimResults = true;
            rec.maxAlternatives = 1;
            rec.lang = this.voiceLang;

            rec.onresult = (e) => {
                let interim = '', final_ = '';
                for (let i = e.resultIndex; i < e.results.length; i++) {
                    if (e.results[i].isFinal) final_ += e.results[i][0].transcript;
                    else interim += e.results[i][0].transcript;
                }
                this.interimTranscript = interim;
                if (final_) {
                    const a = this.$refs.promptArea;
                    a.value = a.value + (a.value.length > 0 && !a.value.endsWith(' ') ? ' ' : '') + final_;
                    a.dispatchEvent(new Event('input', { bubbles: true }));
                    this.interimTranscript = '';
                }
            };
            rec.onerror = (e) => {
                this.isListening = false;
                const msgs = {
                    'not-allowed': 'Accès au microphone refusé. Autorisez le micro dans les paramètres de votre navigateur.',
                    'network': 'La reconnaissance vocale nécessite une connexion internet stable et HTTPS.',
                    'no-speech': 'Aucune parole détectée. Parlez plus fort ou rapprochez-vous du micro.',
                    'audio-capture': 'Aucun microphone trouvé. Branchez un micro et réessayez.',
                    'aborted': '',
                };
                const msg = msgs[e.error];
                if (msg === '') return;
                this.voiceError = msg || ('Erreur vocale : ' + e.error);
                setTimeout(() => this.voiceError = '', 8000);
            };
            rec.onend = () => { this.isListening = false; this.interimTranscript = ''; };
            this.recognition = rec;
        },

        toggleVoice() {
            if (!this.recognition) return;
            if (this.isListening) { this.recognition.abort(); this.isListening = false; return; }
            this.voiceError = '';
            this.recognition.lang = this.voiceLang;
            try { this.recognition.start(); this.isListening = true; }
            catch(e) { this.voiceError = 'Impossible de démarrer le micro. Rechargez la page.'; }
        },
        saveVoiceLang() { try { localStorage.setItem('voiceLang', this.voiceLang); } catch(e) {} },

        handleFiles(e) { this.addFiles(Array.from(e.target.files)); },
        handleDrop(e) { this.dragOver = false; this.addFiles(Array.from(e.dataTransfer.files)); },
        addFiles(nf) {
            this.fileError = '';
            for (const f of nf) {
                if (this.files.length >= 5) { this.fileError = '5 fichiers maximum.'; break; }
                if (f.size > 10*1024*1024) { this.fileError = '« '+f.name+' » dépasse 10 Mo.'; continue; }
                this.files.push(f);
            }
            this.totalSize = this.files.reduce((s,f)=>s+f.size,0);
            if (this.totalSize > 25*1024*1024) this.fileError = 'Taille totale > 25 Mo.';
            this.syncFileInput();
        },
        removeFile(i) { this.files.splice(i,1); this.totalSize = this.files.reduce((s,f)=>s+f.size,0); this.fileError = ''; this.syncFileInput(); },
        syncFileInput() { const dt = new DataTransfer(); this.files.forEach(f=>dt.items.add(f)); this.$refs.fileInput.files = dt.files; },
        formatSize(b) { if(b<1024) return b+' o'; if(b<1048576) return (b/1024).toFixed(1)+' Ko'; return (b/1048576).toFixed(1)+' Mo'; },
        submitForm(e) {
            if (this.fileError) return;
            if (this.isListening && this.recognition) this.recognition.abort();
            this.loading = true;
            this.$nextTick(() => e.target.submit());
        },
    };
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/LTH/Stage/mini-lms/v2/mini-lms/resources/views/admin/ai/create.blade.php ENDPATH**/ ?>