<?php $__env->startSection('title', $post->title); ?>

<?php $__env->startSection('content'); ?>
    
    <article style="padding:1.5rem;border:1px solid #eee;border-radius:4px;margin-bottom:2rem;">
        <h1 style="margin:0 0 0.5rem;"><?php echo e($post->title); ?></h1>
        
        <div style="color:#666;font-size:0.9rem;margin-bottom:1rem;">
            Автор: <strong><?php echo e($post->author->name); ?></strong> · 
            <?php echo e($post->created_at->format('d.m.Y H:i')); ?>

            <?php if($post->created_at != $post->updated_at): ?>
                · обновлено <?php echo e($post->updated_at->format('d.m.Y H:i')); ?>

            <?php endif; ?>
        </div>

        <div style="white-space:pre-wrap;line-height:1.6;">
            <?php echo e($post->body); ?>

        </div>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $post)): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #eee;">
                <a href="<?php echo e(route('posts.edit', $post)); ?>" 
                   style="display:inline-block;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;">
                    Редактировать
                </a>
                <form action="<?php echo e(route('posts.destroy', $post)); ?>" method="POST" 
                      style="display:inline;margin-left:0.5rem;"
                      onsubmit="return confirm('Удалить пост?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" 
                            style="padding:0.5rem 1rem;background:#dc3545;color:white;border:none;border-radius:4px;cursor:pointer;">
                        Удалить
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </article>

    
    <div class="comments">
        <h3>Комментарии (<?php echo e($post->comments->count()); ?>)</h3>

        <?php $__empty_1 = true; $__currentLoopData = $post->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="comment" style="padding:1rem;background:#f9f9f9;border-radius:4px;margin-bottom:0.75rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong><?php echo e($comment->author->name); ?></strong>
                    <small style="color:#666;"><?php echo e($comment->created_at->format('d.m.Y H:i')); ?></small>
                </div>
                <p style="margin:0.5rem 0 0;"><?php echo e($comment->body); ?></p>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $comment)): ?>
                    <form action="<?php echo e(route('comments.destroy', $comment)); ?>" method="POST"
                          onsubmit="return confirm('Удалить комментарий?')"
                          style="display:inline;margin-top:0.5rem;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" 
                                style="padding:0.25rem 0.5rem;font-size:0.875rem;background:#dc3545;color:white;border:none;border-radius:4px;cursor:pointer;">
                            Удалить
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p style="color:#666;">Комментариев пока нет.</p>
        <?php endif; ?>

        
        <?php if(auth()->guard()->check()): ?>
            <form action="<?php echo e(route('comments.store')); ?>" method="POST" style="margin-top:1.5rem;">
                <?php echo csrf_field(); ?>
                
                <input type="hidden" name="post_id" value="<?php echo e($post->id); ?>">

                <textarea name="body" rows="3"
                          placeholder="Напишите комментарий..."
                          required
                          maxlength="1000"
                          style="width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:4px;font:inherit;"><?php echo e(old('body')); ?></textarea>

                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:#dc3545;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <button type="submit" 
                        style="margin-top:0.5rem;padding:0.5rem 1rem;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;">
                    Отправить
                </button>
            </form>
        <?php else: ?>
            <p style="margin-top:1rem;color:#666;">
                <a href="<?php echo e(route('login')); ?>" style="color:#007bff;">Войдите</a>, чтобы оставить комментарий.
            </p>
        <?php endif; ?>
    </div>

    <p style="margin-top:2rem;">
        <a href="<?php echo e(route('posts.index')); ?>" style="color:#007bff;">← Назад к списку постов</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/boardy/resources/views/posts/show.blade.php ENDPATH**/ ?>