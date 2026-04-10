<?php $__env->startSection('content'); ?>
<div class="fade-in">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div class="min-w-0">
            <a href="<?php echo e(route('admin.formations.index')); ?>" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Formations
            </a>
            <h1 class="text-2xl font-bold text-slate-900 break-words"><?php echo e($formation->name); ?></h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-<?php echo e($formation->level_badge_color); ?>-100 text-<?php echo e($formation->level_badge_color); ?>-700"><?php echo e(ucfirst($formation->level)); ?></span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium <?php echo e($formation->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'); ?>"><?php echo e($formation->status === 'published' ? 'Publié' : 'Brouillon'); ?></span>
                <?php if($formation->duration_hours): ?><span class="text-xs text-slate-400"><?php echo e($formation->duration_hours); ?>h</span><?php endif; ?>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.formations.edit', $formation)); ?>" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg px-3 py-2 hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Modifier
            </a>
            <form method="POST" action="<?php echo e(route('admin.formations.destroy', $formation)); ?>" onsubmit="return confirm('Supprimer cette formation et tout son contenu ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="inline-flex items-center gap-1.5 text-sm text-rose-600 hover:text-rose-700 border border-rose-200 rounded-lg px-3 py-2 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <?php if($formation->description): ?>
        <p class="text-sm text-slate-600 mb-8 max-w-3xl"><?php echo e($formation->description); ?></p>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Contenu</h2>
                <a href="<?php echo e(route('admin.chapters.create', $formation)); ?>" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajouter un chapitre
                </a>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $formation->chapters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chapter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" x-data="{ open: true }">
                <div class="px-5 py-4 flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 font-semibold text-sm flex-shrink-0"><?php echo e($chapter->order); ?></div>
                        <div class="min-w-0">
                            <h3 class="font-medium text-slate-900"><?php echo e($chapter->title); ?></h3>
                            
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs text-slate-400"><?php echo e($chapter->subChapters->count()); ?> sous-chapitre(s)</span>
                                <?php if($chapter->image_url): ?>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="Illustration disponible">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Illustration
                                    </span>
                                <?php endif; ?>
                                <?php if($chapter->sources && is_array($chapter->sources) && count($chapter->sources) > 0): ?>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="<?php echo e(count($chapter->sources)); ?> source(s) de référence">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <?php echo e(count($chapter->sources)); ?> source(s)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="<?php echo e(route('admin.chapters.edit', [$formation, $chapter])); ?>" class="text-slate-400 hover:text-slate-600 p-1" onclick="event.stopPropagation()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form method="POST" action="<?php echo e(route('admin.chapters.destroy', [$formation, $chapter])); ?>" onsubmit="return confirm('Supprimer ce chapitre ?')" onclick="event.stopPropagation()">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-slate-400 hover:text-rose-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </form>
                        <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div x-show="open" x-transition class="border-t border-slate-100">

                    
                    <?php if($chapter->image_url): ?>
                    <div class="px-5 pt-4">
                        <img src="<?php echo e($chapter->image_url); ?>" alt="<?php echo e($chapter->image_alt ?? $chapter->title); ?>" loading="lazy"
                            class="w-full rounded-lg border border-slate-200 object-cover max-h-40">
                        <?php if($chapter->image_credit): ?>
                        <p class="mt-1 text-xs text-slate-400 text-center italic"><?php echo e($chapter->image_credit); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($chapter->sources && is_array($chapter->sources) && count($chapter->sources) > 0): ?>
                    <div class="px-5 pt-3 flex flex-wrap gap-2">
                        <?php $__currentLoopData = $chapter->sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(isset($source['url']) && isset($source['title'])): ?>
                            <a href="<?php echo e($source['url']); ?>" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded text-xs text-slate-600 hover:border-brand-300 hover:text-brand-700 transition-colors">
                                <?php if(($source['type'] ?? '') === 'wikipedia'): ?>
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <?php elseif(($source['type'] ?? '') === 'docs'): ?>
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <?php else: ?>
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <?php endif; ?>
                                <span class="truncate max-w-[120px]"><?php echo e($source['title']); ?></span>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>

                    
                    <?php $__currentLoopData = $chapter->subChapters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 border-b border-slate-50 last:border-b-0">
                        <a href="<?php echo e(route('admin.subchapters.show', [$chapter, $sub])); ?>" class="flex items-center gap-3 flex-1 group">
                            <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500 text-xs"><?php echo e($sub->order); ?></div>
                            <div>
                                <p class="text-sm font-medium text-slate-700 group-hover:text-brand-600"><?php echo e($sub->title); ?></p>
                                
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <?php if($sub->quiz): ?>
                                        <span class="text-xs text-emerald-600 font-medium">Quiz : <?php echo e($sub->quiz->title); ?></span>
                                    <?php endif; ?>
                                    <?php if($sub->image_url): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="Illustration disponible">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Illustration
                                        </span>
                                    <?php endif; ?>
                                    <?php if($sub->sources && is_array($sub->sources) && count($sub->sources) > 0): ?>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="<?php echo e(count($sub->sources)); ?> source(s)">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            <?php echo e(count($sub->sources)); ?> source(s)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <?php if(!$sub->quiz): ?>
                                <a href="<?php echo e(route('admin.quizzes.create', $sub)); ?>" class="text-xs text-brand-600 hover:text-brand-700 px-2 py-1 rounded hover:bg-brand-50">+ Quiz</a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('admin.subchapters.edit', [$chapter, $sub])); ?>" class="text-slate-400 hover:text-slate-600 p-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="px-5 py-3">
                        <a href="<?php echo e(route('admin.subchapters.create', $chapter)); ?>" class="text-xs text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajouter un sous-chapitre
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-sm text-slate-400">Aucun chapitre. Commencez par en ajouter un.</p>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-900">Apprenants inscrits (<?php echo e($formation->students->count()); ?>)</h2>
                </div>

                <?php if($availableStudents->count()): ?>
                <form method="POST" action="<?php echo e(route('admin.formations.enroll', $formation)); ?>" class="px-5 py-3 border-b border-slate-100">
                    <?php echo csrf_field(); ?>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select name="user_id" required class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500">
                            <option value="">Choisir...</option>
                            <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($student->id); ?>"><?php echo e($student->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm px-3 py-2 rounded-lg transition-colors">Inscrire</button>
                    </div>
                </form>
                <?php endif; ?>

                <?php $__empty_1 = true; $__currentLoopData = $formation->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-5 py-3 flex items-center justify-between border-b border-slate-50 last:border-b-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-xs font-semibold"><?php echo e(strtoupper(substr($student->name, 0, 1))); ?></div>
                        <span class="text-sm text-slate-700"><?php echo e($student->name); ?></span>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.formations.unenroll', [$formation, $student])); ?>" onsubmit="return confirm('Désinscrire cet apprenant ?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-xs text-rose-500 hover:text-rose-700">Retirer</button>
                    </form>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-5 py-6 text-center text-sm text-slate-400">Aucun apprenant inscrit.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/LTH/Stage/mini-lms/v2/mini-lms/resources/views/admin/formations/show.blade.php ENDPATH**/ ?>