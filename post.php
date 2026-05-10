<?php
require_once 'config.php';

$post_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT p.*, u.username, u.nickname, u.avatar, c.name as cate_name 
                       FROM post p 
                       LEFT JOIN user u ON p.user_id = u.id 
                       LEFT JOIN category c ON p.cate_id = c.id 
                       WHERE p.id = ? AND p.status = 1");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    die("帖子不存在或已被删除");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO comment (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $content]);
        header("Location: post.php?id=$post_id");
        exit;
    }
}

$comments_sql = "SELECT c.*, u.username, u.nickname, u.avatar 
                 FROM comment c 
                 LEFT JOIN user u ON c.user_id = u.id 
                 WHERE c.post_id = ? 
                 ORDER BY c.create_time ASC";
$stmt = $pdo->prepare($comments_sql);
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($post['title']); ?> - 小魔头Galgame论坛</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=ZCOOL+KuaiLe&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a1423 0%, #2c1b54 50%, #1a1423 100%);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .top-title {
            width: 100%;
            padding: 30px 20px;
            background: linear-gradient(135deg, #4b2e83, #2c1b54);
            text-align: center;
            border-bottom: 4px solid #c9a8ff;
        }
        .top-title h1 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 42px;
            letter-spacing: 4px;
            color: #ffe699;
            text-shadow: 3px 3px 8px #000;
        }
        .nav {
            display: flex;
            width: 100%;
            background: #251a3d;
        }
        .nav a {
            flex: 1;
            text-align: center;
            padding: 14px 0;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            border-right: 1px solid #3c2b63;
            transition: background 0.3s;
        }
        .nav a:hover { background: #583ea1; }
        .user-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 20px;
            background: rgba(37, 26, 61, 0.9);
            gap: 15px;
        }
        .user-bar a {
            color: #c9a8ff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 6px;
            background: #7c52d6;
            transition: all 0.3s;
            font-size: 14px;
        }
        .user-bar a:hover { background: #946aff; }
        .user-info { color: #ffd878; font-size: 14px; }
        .main {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
            color: #9988bb;
        }
        .breadcrumb a {
            color: #c9a8ff;
            text-decoration: none;
        }
        .breadcrumb a:hover { color: #ffd878; }
        .post-content {
            background: #271c44;
            border-radius: 12px;
            border: 1px solid #6348a0;
            padding: 30px;
            margin-bottom: 20px;
        }
        .post-header {
            border-bottom: 2px solid #3d2e66;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .post-header h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 28px;
            color: #ffd878;
            margin-bottom: 15px;
        }
        .post-meta {
            font-size: 14px;
            color: #9988bb;
        }
        .post-meta span { margin-right: 20px; }
        .post-body {
            line-height: 1.8;
            font-size: 16px;
            color: #e0d4f5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .download-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #3d2e66;
        }
        .download-section h3 {
            color: #ffd878;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .download-btn {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: #fff;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #34ce57, #28a745);
            transform: translateY(-2px);
        }
        .download-info {
            margin-top: 10px;
            font-size: 14px;
            color: #9988bb;
        }
        .comments-section {
            background: #271c44;
            border-radius: 12px;
            border: 1px solid #6348a0;
            padding: 25px;
        }
        .comments-section h3 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 24px;
            color: #ffd878;
            margin-bottom: 20px;
            border-left: 4px solid #c9a8ff;
            padding-left: 12px;
        }
        .comment-form {
            margin-bottom: 25px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #3d2e66;
            border-radius: 8px;
            background: #1a1423;
            color: #fff;
            font-size: 15px;
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }
        .comment-form textarea:focus {
            outline: none;
            border-color: #7c52d6;
        }
        .submit-btn {
            background: linear-gradient(135deg, #7c52d6, #5e3cad);
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, #946aff, #7c52d6);
        }
        .comment-list {
            margin-top: 20px;
        }
        .comment-item {
            padding: 20px;
            border-bottom: 1px solid #3d2e66;
        }
        .comment-item:last-child { border-bottom: none; }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment-author {
            color: #c9a8ff;
            font-weight: bold;
        }
        .comment-time {
            color: #9988bb;
            font-size: 13px;
        }
        .comment-body {
            line-height: 1.7;
            color: #e0d4f5;
        }
        .empty-comments {
            text-align: center;
            padding: 40px;
            color: #9988bb;
        }
        footer {
            text-align: center;
            padding: 25px;
            color: #9988bb;
            font-size: 14px;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .top-title h1 { font-size: 32px; }
            .post-header h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="top-title">
        <h1>小魔头Galgame</h1>
    </div>

    <div class="nav">
        <a href="index.php">首页</a>
        <a href="category.php?id=<?php echo $post['cate_id']; ?>"><?php echo sanitize($post['cate_name']); ?></a>
        <a href="#">游戏资源</a>
        <a href="#">文件下载</a>
    </div>

    <?php if (isLoggedIn()): ?>
    <div class="user-bar">
        <span class="user-info">欢迎，<?php echo sanitize($_SESSION['nickname']); ?></span>
        <?php if (isAdmin()): ?>
            <a href="admin/index.php">管理后台</a>
        <?php endif; ?>
        <a href="logout.php">退出</a>
    </div>
    <?php else: ?>
    <div class="user-bar">
        <a href="login.php">登录</a>
        <a href="register.php">注册</a>
    </div>
    <?php endif; ?>

    <div class="main">
        <div class="breadcrumb">
            <a href="index.php">首页</a> &gt; 
            <a href="category.php?id=<?php echo $post['cate_id']; ?>"><?php echo sanitize($post['cate_name']); ?></a> &gt; 
            当前帖子
        </div>

        <div class="post-content">
            <div class="post-header">
                <h2><?php echo sanitize($post['title']); ?></h2>
                <div class="post-meta">
                    <span>作者：<?php echo sanitize($post['nickname'] ?: $post['username']); ?></span>
                    <span>发布时间：<?php echo date('Y-m-d H:i', strtotime($post['create_time'])); ?></span>
                    <span>下载次数：<?php echo $post['download_count']; ?></span>
                </div>
            </div>

            <div class="post-body"><?php echo sanitize($post['content']); ?></div>

            <?php if ($post['file_path']): ?>
            <div class="download-section">
                <h3>📎 附件下载</h3>
                <a href="download.php?id=<?php echo $post_id; ?>" class="download-btn">下载文件</a>
                <div class="download-info">
                    文件名：<?php echo sanitize($post['file_name']); ?> | 
                    文件大小：<?php echo sanitize($post['file_size']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h3>评论 (<?php echo count($comments); ?>)</h3>

            <?php if (isLoggedIn()): ?>
            <div class="comment-form">
                <form method="POST" action="">
                    <textarea name="content" placeholder="写下你的评论..." required></textarea>
                    <button type="submit" class="submit-btn">发表评论</button>
                </form>
            </div>
            <?php else: ?>
            <p style="text-align:center;color:#9988bb;margin-bottom:20px;">
                <a href="login.php" style="color:#c9a8ff;">登录</a>后才能发表评论
            </p>
            <?php endif; ?>

            <div class="comment-list">
                <?php if (empty($comments)): ?>
                    <div class="empty-comments">暂无评论，来抢沙发吧！</div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author"><?php echo sanitize($comment['nickname'] ?: $comment['username']); ?></span>
                            <span class="comment-time"><?php echo timeAgo($comment['create_time']); ?></span>
                        </div>
                        <div class="comment-body"><?php echo sanitize($comment['content']); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        小魔头Galgame论坛 · 全站免费资源
    </footer>
</body>
</html>