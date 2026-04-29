<?php
// Ожидается, что session_start() уже вызван в родительском файле

$is_logged = !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>

<nav style="background:#1a3a52; padding:12px 30px; display:flex; justify-content:space-between; align-items:center;">
    <div style="display:flex; align-items:center; gap:25px;">
        <a href="/" style="color:white; text-decoration:none; font-weight:bold; font-size:18px;">Boardy</a>
        <a href="/messages.php" style="color:#e0e0e0; text-decoration:none; font-size:14px;">Все посты</a>
        
        <?php if ($is_logged): ?>
            <a href="/submit.php" style="color:#e0e0e0; text-decoration:none; font-size:14px;">Добавить пост</a>
        <?php endif; ?>
    </div>
    
    <div style="display:flex; align-items:center; gap:15px;">
        <?php if ($is_logged): ?>
            <span style="color:#e0e0e0; font-size:14px;">
                Привет, <?= htmlspecialchars($user_name) ?>!
            </span>
            <a href="/logout.php" style="color:#e0e0e0; text-decoration:none; font-size:14px; border:1px solid #e0e0e0; padding:6px 15px; border-radius:4px;">
                Выйти
            </a>
        <?php else: ?>
            <a href="/login.php" style="color:#e0e0e0; text-decoration:none; font-size:14px; border:1px solid #e0e0e0; padding:6px 15px; border-radius:4px;">
                Вход
            </a>
            <a href="/register.php" style="color:white; text-decoration:none; font-size:14px; background:#2c5282; padding:6px 15px; border-radius:4px;">
                Регистрация
            </a>
	    <a href="/oauth-github.php" 
           	style="background:#24292e;color:#fff;padding:6px 12px;text-decoration:none;border-radius:5px;font-size:14px;">
           	Войти через GitHub
       	    </a>
        <?php endif; ?>
    </div>
</nav>
