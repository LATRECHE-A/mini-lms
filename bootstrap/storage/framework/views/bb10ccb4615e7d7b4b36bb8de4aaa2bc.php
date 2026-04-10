<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">IA Playground</h1>
            <p class="text-sm text-slate-500 mt-1">Générez du contenu pédagogique avec l'IA</p>
        </div>
        <a href="<?php echo e(route('admin.ai.create')); ?>" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nouvelle génération
        </a>
    </div>

    <?php if(!$isAvailable): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6 text-sm">
        Le service IA n'est pas configuré. Définissez <code class="bg-amber-100 px-1 rounded">GEMINI_API_KEY</code> dans votre fichier .env.
    </div>
    <?php endif; ?>

    <div class="mb-10">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Mes générations</h2>

        <?php if($generations->isEmpty()): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                <p class="text-slate-500 mb-4">Aucune génération pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $generations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('admin.ai.show', $gen)); ?>" class="block bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 group-hover:text-brand-700 truncate"><?php echo e(Str::limit($gen->prompt, 80)); ?></p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                <span class="capitalize"><?php echo e($gen->type); ?></span>
                                <span><?php echo e($gen->created_at->diffForHumans()); ?></span>
                                <?php if($gen->updated_at->gt($gen->created_at->addSeconds(5))): ?>
                                    <span class="text-amber-500">· modifié <?php echo e($gen->updated_at->diffForHumans()); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($gen->status_color); ?>-100 text-<?php echo e($gen->status_color); ?>-700">
                            <?php echo e($gen->status_label); ?>

                        </span>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="mt-4"><?php echo e($generations->links()); ?></div>
        <?php endif; ?>
    </div>

    <?php if($studentValidated->isNotEmpty()): ?>
    <div>
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Contenu validé par les apprenants</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700"><?php echo e($studentValidated->count()); ?></span>
        </div>

        <div class="space-y-3">
            <?php $__currentLoopData = $studentValidated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('admin.ai.show', $gen)); ?>" class="block bg-white rounded-xl border border-sky-200 p-5 hover:border-sky-300 hover:shadow-md transition-all group">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate"><?php echo e(Str::limit($gen->prompt, 80)); ?></p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                            <span class="text-sky-600 font-medium"><?php echo e($gen->user->name); ?></span>
                            <span class="capitalize"><?php echo e($gen->type); ?></span>
                            <span>Validé <?php echo e($gen->validated_at?->diffForHumans()); ?></span>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        Validé
                    </span>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/LTH/Stage/mini-lms/v2/mini-lms/resources/views/admin/ai/index.blade.php ENDPATH**/ ?>