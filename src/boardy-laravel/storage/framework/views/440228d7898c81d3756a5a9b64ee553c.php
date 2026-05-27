<?php $__env->startSection('title', 'Все посты'); ?>

<?php $__env->startSection('content'); ?>
    <h1>Все посты</h1>
<div id="posts-feed"> 
    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article>
            <h3>
                <a href="<?php echo e(route('posts.show', $post)); ?>"><?php echo e($post->title); ?></a>
            </h3>
            <p><?php echo e(Str::limit($post->body, 200)); ?></p>
            <small>
                Автор: <?php echo e($post->author->name); ?> · 
                <?php echo e($post->created_at->format('d.m.Y H:i')); ?> · 
                Комментариев: <?php echo e($post->comments->count()); ?>

            </small>
        </article>
     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div> 
    <?php if($posts->hasPages()): ?>
        <div style="margin-top:1rem;">
            <?php echo e($posts->links()); ?>

        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<script> 

const wsUrl = 'wss://api.<?php echo e(config("app.domain")); ?>/ws'; 

 

function connect() { 

    const ws = new WebSocket(wsUrl); 
    ws.onopen = () => { 

        console.log('WS connected'); 

    }; 

    ws.onmessage = (event) => { 
        const msg = JSON.parse(event.data); 
        if (msg.type === 'new_post') { 
            prependPost(msg.post); 
        } 
    }; 

    ws.onclose = () => { 
        console.log('WS closed, reconnecting...'); 
        setTimeout(connect, 3000); 
    }; 

} 

 

function prependPost(post) { 

    const feed = document.getElementById('posts-feed'); 

    if (!feed) return; 

    const div = document.createElement('div'); 

    div.className = 'post-card'; 

    div.innerHTML = ` 

        <h3>${escapeHtml(post.title)}</h3> 

        <p>${escapeHtml(post.body)}</p> 

        <small>${escapeHtml(post.author)} � ������ ���</small> 

    `; 
    feed.prepend(div); 
} 

 

function escapeHtml(str) { 
    const d = document.createElement('div'); 
    d.textContent = str; 
    return d.innerHTML; 

} 

connect(); 
</script> 

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/boardy/resources/views/posts/index.blade.php ENDPATH**/ ?>