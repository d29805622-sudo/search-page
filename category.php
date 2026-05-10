<?php
require_once 'config.php';

$category_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM category WHERE id = ? AND status = 1");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    die("板块不存在");
}

$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_sql = "SELECT COUNT(*) FROM post WHERE cate_id = ? AND status = 1 AND is_check = 1";
$total = $pdo->prepare($count_sql)->execute([$category_id]) ? $pdo->query($count_sql)->fetchColumn() : 0;
$total_pages = ceil($total / $per_page);

$posts_sql = "SELECT p.*, u.username, u.nickname, u.avatar 
              FROM post p 
              LEFT JOIN user u ON p.user_id = u.id 
              WHERE p.cate_id = ? AND p.status = 1 AND p.is_check = 1 
              ORDER BY p.create_time DESC 
              LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($posts_sql);
$stmt->execute([$category_id]);
$posts = $stmt->fetchAll();

$comment_count_sql = "SELECT post_id, COUNT(*) as count FROM comment GROUP BY post_id";
$comment_counts = $pdo->query($comment_count_sql)->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($category['name']); ?> - 小魔头Galgame论坛</title>
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
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-header h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 28px;
            color: #ffd878;
        }
        .new-post-btn {
            background: linear-gradient(135deg, #7c52d6, #5e3cad);
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .new-post-btn:hover {
            background: linear-gradient(135deg, #946aff, #7c52d6);
            transform: translateY(-2px);
        }
        .post-list {
            background: #271c44;
            border-radius: 12px;
            border: 1px solid #6348a0;
            overflow: hidden;
        }
        .post-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            border-bottom: 1px solid #3d2e66;
            transition: background 0.3s;
        }
        .post-item:last-child { border-bottom: none; }
        .post-item:hover { background: rgba(124, 82, 214, 0.1); }
        .post-info h3 {
            font-size: 17px;
            margin-bottom: 6px;
        }
        .post-info h3 a {
            color: #e0d4f5;
            text-decoration: none;
            transition: color 0.3s;
        }
        .post-info h3 a:hover { color: #ffd878; }
        .post-meta {
            font-size: 13px;
            color: #9988bb;
        }
        .post-meta span { margin-right: 15px; }
        .post-stats {
            text-align: right;
            color: #9988bb;
            font-size: 13px;
        }
        .post-stats div { margin-bottom: 4px; }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 14px;
            background: #271c44;
            color: #c9a8ff;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #6348a0;
        }
        .pagination a:hover { background: #7c52d6; }
        .pagination .current { background: #7c52d6; }
        .empty {
            text-align: center;
            padding: 60px 20px;
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
            .page-header { flex-direction: column; gap: 15px; }
            .post-item { flex-direction: column; text-align: center; gap: 10px; }
            .post-stats { text-align: center; }
        }
    </style>
</head>
<body>
    <div class="top-title">
        <h1>小魔头Galgame</h1>
    </div>

    <div class="nav">
        <a href="index.php">首页</a>
        <a href="category.php?id=<?php echo $category_id; ?>"><?php echo sanitize($category['name']); ?></a>
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
        <div class="page-header">
            <h2><?php echo sanitize($category['name']); ?></h2>
            <?php if (isLoggedIn()): ?>
                <a href="new_post.php?cate_id=<?php echo $category_id; ?>" class="new-post-btn">发布帖子</a>
            <?php else: ?>
                <a href="login.php" class="new-post-btn">登录后发帖</a>
            <?php endif; ?>
        </div>

        <div class="post-list">
            <?php if (empty($posts)): ?>
                <div class="empty">
                    <p>暂无帖子，成为第一个发帖的人吧！</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <div class="post-info">
                        <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo sanitize($post['title']); ?></a></h3>
                        <div class="post-meta">
                            <span>作者：<?php echo sanitize($post['nickname'] ?: $post['username']); ?></span>
                            <span><?php echo timeAgo($post['create_time']); ?></span>
                            <?php if ($post['file_path']): ?>
                                <span style="color: #7c52d6;">📎 附件</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="post-stats">
                        <div>评论：<?php echo $comment_counts[$post['id']] ?? 0; ?></div>
                        <div>下载：<?php echo $post['download_count']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>">上一页</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>">下一页</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        小魔头Galgame论坛 · 全站免费资源
    </footer>
</body>
</html>