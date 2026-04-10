<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="mb-8">
        <a href="<?php echo e(route('admin.formations.show', $chapter->formation)); ?>" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <?php echo e($chapter->formation->name); ?> → <?php echo e($chapter->title); ?>

        </a>
        <h1 class="text-2xl font-bold text-slate-900"><?php echo e($subchapter->title); ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="font-semibold text-slate-900 mb-4">Contenu pédagogique</h2>

                
                <?php if($subchapter->image_url): ?>
                <figure class="mb-6">
                    <img src="<?php echo e($subchapter->image_url); ?>" alt="<?php echo e($subchapter->image_alt ?? $subchapter->title); ?>" loading="lazy"
                        class="w-full rounded-lg border border-slate-200 object-cover max-h-80">
                    <?php if($subchapter->image_credit): ?>
                    <figcaption class="mt-2 text-xs text-slate-400 text-center italic"><?php echo e($subchapter->image_credit); ?></figcaption>
                    <?php endif; ?>
                </figure>
                <?php endif; ?>

                
                <?php if($subchapter->mermaid_code): ?>
                <div class="mb-6 bg-slate-50 rounded-lg border border-slate-200 p-4 overflow-x-auto">
                    <p class="text-xs text-slate-500 mb-3 flex items-center gap-1.5 font-medium">
                        <span>📊</span> Diagramme explicatif
                    </p>
                    <pre class="mermaid text-sm"><?php echo e($subchapter->mermaid_code); ?></pre>
                </div>
                <?php endif; ?>

                <?php if($subchapter->content): ?>
                    <div class="prose prose-sm prose-slate max-w-none">
                        <?php echo \App\Services\ContentSanitizer::render($subchapter->content); ?>

                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-400 italic">Aucun contenu défini.</p>
                <?php endif; ?>

                
                <?php if($subchapter->sources && is_array($subchapter->sources) && count($subchapter->sources) > 0): ?>
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <span>📚</span> Sources et références
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <?php $__currentLoopData = $subchapter->sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($source['url']); ?>" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-4 py-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-brand-300 hover:bg-brand-50 transition-colors group">
                            <span class="text-lg flex-shrink-0">
                                <?php if(($source['type'] ?? '') === 'wikipedia'): ?> 📖
                                <?php elseif(($source['type'] ?? '') === 'docs'): ?> 📄
                                <?php else: ?> 🔗
                                <?php endif; ?>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-700 truncate"><?php echo e($source['title'] ?? $source['url']); ?></p>
                                <p class="text-xs text-slate-400 truncate"><?php echo e(parse_url($source['url'], PHP_URL_HOST)); ?></p>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-500 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Actions</h3>
                <div class="space-y-2">
                    <a href="<?php echo e(route('admin.subchapters.edit', [$chapter, $subchapter])); ?>" class="block text-sm text-brand-600 hover:text-brand-700">Modifier le contenu</a>
                    <?php if($subchapter->quiz): ?>
                        <a href="<?php echo e(route('admin.quizzes.show', $subchapter->quiz)); ?>" class="block text-sm text-brand-600 hover:text-brand-700">Voir le quiz (<?php echo e($subchapter->quiz->questions->count()); ?> questions)</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('admin.quizzes.create', $subchapter)); ?>" class="block text-sm text-brand-600 hover:text-brand-700">Créer un quiz</a>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($subchapter->image_url || ($subchapter->sources && count($subchapter->sources) > 0) || $subchapter->mermaid_code): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900 mb-3">Enrichissement IA</h3>
                <div class="space-y-2 text-sm text-slate-600">
                    <?php if($subchapter->image_url): ?>
                    <div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Image illustrative</div>
                    <?php endif; ?>
                    <?php if($subchapter->mermaid_code): ?>
                    <div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Diagramme Mermaid</div>
                    <?php endif; ?>
                    <?php if($subchapter->sources && count($subchapter->sources) > 0): ?>
                    <div class="flex items-center gap-2"><span class="text-emerald-500">✓</span> <?php echo e(count($subchapter->sources)); ?> source(s)</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($subchapter->mermaid_code): ?>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'strict' });</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/LTH/Stage/mini-lms/v2/mini-lms/resources/views/admin/subchapters/show.blade.php ENDPATH**/ ?>