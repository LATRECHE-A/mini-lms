<?php $__env->startSection('content'); ?>
<div class="fade-in max-w-3xl">
    <div class="mb-8">
        <a href="<?php echo e(route('admin.formations.show', $chapter->formation)); ?>" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <?php echo e($chapter->formation->name); ?>

        </a>
        <h1 class="text-2xl font-bold text-slate-900">Modifier « <?php echo e($subchapter->title); ?> »</h1>
    </div>

    <form method="POST" action="<?php echo e(route('admin.subchapters.update', [$chapter, $subchapter])); ?>" class="space-y-6">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <h2 class="font-semibold text-slate-900 text-sm">Informations générales</h2>
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1.5">Titre <span class="text-rose-500">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo e(old('title', $subchapter->title)); ?>" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-slate-700 mb-1.5">Contenu (HTML)</label>
                <textarea id="content" name="content" rows="12" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"><?php echo e(old('content', $subchapter->content)); ?></textarea>
                <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="order" class="block text-sm font-medium text-slate-700 mb-1.5">Ordre</label>
                <input type="number" id="order" name="order" value="<?php echo e(old('order', $subchapter->order)); ?>" min="0" class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">🖼️ Image illustrative</h2>
            <?php if($subchapter->image_url): ?>
            <div>
                <img src="<?php echo e($subchapter->image_url); ?>" alt="<?php echo e($subchapter->image_alt); ?>" class="max-h-40 rounded-lg border border-slate-200">
                <?php if($subchapter->image_credit): ?>
                <p class="mt-1 text-xs text-slate-400 italic"><?php echo e($subchapter->image_credit); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div>
                <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1.5">URL de l'image</label>
                <input type="url" id="image_url" name="image_url" value="<?php echo e(old('image_url', $subchapter->image_url)); ?>" placeholder="https://..." class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <?php $__errorArgs = ['image_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="image_alt" class="block text-sm font-medium text-slate-700 mb-1.5">Texte alternatif</label>
                    <input type="text" id="image_alt" name="image_alt" value="<?php echo e(old('image_alt', $subchapter->image_alt)); ?>" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label for="image_credit" class="block text-sm font-medium text-slate-700 mb-1.5">Crédit image</label>
                    <input type="text" id="image_credit" name="image_credit" value="<?php echo e(old('image_credit', $subchapter->image_credit)); ?>" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📚 Sources et références</h2>
            <p class="text-xs text-slate-400">Format JSON : tableau d'objets avec "title", "url", "type" (docs/wikipedia/article).</p>
            <textarea id="sources_json" name="sources_json" rows="5" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder='[{"title":"Documentation","url":"https://...","type":"docs"}]'><?php echo e(old('sources_json', $subchapter->sources ? json_encode($subchapter->sources, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')); ?></textarea>
            <?php $__errorArgs = ['sources_json'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <?php if($subchapter->sources && count($subchapter->sources) > 0): ?>
            <div class="flex flex-wrap gap-2">
                <?php $__currentLoopData = $subchapter->sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($src['url']); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded text-xs text-slate-600 hover:border-brand-300 hover:text-brand-700 transition-colors">
                    <?php if(($src['type'] ?? '') === 'wikipedia'): ?> 📖 <?php elseif(($src['type'] ?? '') === 'docs'): ?> 📄 <?php else: ?> 🔗 <?php endif; ?>
                    <?php echo e($src['title'] ?? 'Lien'); ?>

                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <h2 class="font-semibold text-slate-900 text-sm flex items-center gap-2">📊 Diagramme Mermaid</h2>
            <p class="text-xs text-slate-400">Code Mermaid.js (flowchart, sequence, class, mindmap, etc.). Sera rendu visuellement pour les apprenants.</p>
            <textarea id="mermaid_code" name="mermaid_code" rows="8" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="graph TD&#10;    A[Début] --> B[Étape 1]&#10;    B --> C[Fin]"><?php echo e(old('mermaid_code', $subchapter->mermaid_code)); ?></textarea>
            <?php $__errorArgs = ['mermaid_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <?php if($subchapter->mermaid_code): ?>
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <p class="text-xs text-slate-500 mb-2">Aperçu du diagramme :</p>
                <pre class="mermaid text-sm"><?php echo e($subchapter->mermaid_code); ?></pre>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-6 rounded-lg transition-colors">Enregistrer</button>
            <a href="<?php echo e(route('admin.formations.show', $chapter->formation)); ?>" class="text-sm text-slate-500 hover:text-slate-700">Annuler</a>
        </div>
    </form>
</div>

<?php if($subchapter->mermaid_code): ?>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral' });</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/LTH/Stage/mini-lms/v2/mini-lms/resources/views/admin/subchapters/edit.blade.php ENDPATH**/ ?>