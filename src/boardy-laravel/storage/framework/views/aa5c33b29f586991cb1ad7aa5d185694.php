<?php $__env->startSection('title', 'Новый пост'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Новый пост</h1>

    <form action="<?php echo e(route('posts.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div>
            <label for="title"><strong>Заголовок</strong></label>
            <input type="text" name="title" id="title" value="<?php echo e(old('title')); ?>" required maxlength="255">
        </div>

        <div>
            <label for="body"><strong>Текст</strong></label>
            <textarea name="body" id="body" rows="10" required><?php echo e(old('body')); ?></textarea>
        </div>

        <div>
            <button type="submit">Опубликовать</button>
            <a href="<?php echo e(route('posts.index')); ?>" style="margin-left:0.5rem;">Отмена</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/boardy/resources/views/posts/create.blade.php ENDPATH**/ ?>