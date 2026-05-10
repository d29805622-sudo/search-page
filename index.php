<?php
require_once 'config.php';

$categories_sql = "SELECT * FROM category WHERE status = 1 ORDER BY sort ASC";
$categories = $pdo->query($categories_sql)->fetchAll();

$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM user WHERE status = 1) as user_count,
    (SELECT COUNT(*) FROM post WHERE status = 1) as post_count,
    (SELECT COUNT(*) FROM comment) as comment_count
";
$stats = $pdo->query($stats_sql)->fetch();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>小魔头Galgame论坛</title>
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
            padding: 40px 20px;
            background: linear-gradient(135deg, #4b2e83, #2c1b54);
            text-align: center;
            border-bottom: 4px solid #c9a8ff;
        }
        .top-title h1 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 52px;
            letter-spacing: 6px;
            color: #ffe699;
            text-shadow: 3px 3px 8px #000;
        }
        .top-title p {
            font-size: 18px;
            margin-top: 12px;
            color: #e0d4f5;
        }
        .nav {
            display: flex;
            width: 100%;
            background: #251a3d;
        }
        .nav a {
            flex: 1;
            text-align: center;
            padding: 16px 0;
            color: #fff;
            text-decoration: none;
            font-size: 17px;
            border-right: 1px solid #3c2b63;
            transition: background 0.3s;
        }
        .nav a:hover { background: #583ea1; }
        .user-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 12px 20px;
            background: rgba(37, 26, 61, 0.9);
            gap: 15px;
        }
        .user-bar a {
            color: #c9a8ff;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: #7c52d6;
            transition: all 0.3s;
        }
        .user-bar a:hover { background: #946aff; }
        .user-info {
            color: #ffd878;
            font-size: 15px;
        }
        .main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 20px;
            background: rgba(39, 28, 68, 0.8);
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #6348a0;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .num {
            font-size: 32px;
            font-weight: bold;
            color: #ffd878;
        }
        .stat-item .label {
            font-size: 14px;
            color: #9988bb;
            margin-top: 5px;
        }
        .block {
            background: #271c44;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #6348a0;
        }
        .block h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 30px;
            color: #ffd878;
            margin-bottom: 20px;
            border-left: 5px solid #c9a8ff;
            padding-left: 15px;
        }
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #3d2e66;
            transition: background 0.3s;
        }
        .category-item:last-child { border-bottom: none; }
        .category-item:hover { background: rgba(124, 82, 214, 0.1); }
        .category-info h3 {
            font-size: 20px;
            color: #e0d4f5;
            margin-bottom: 8px;
        }
        .category-info p {
            font-size: 14px;
            color: #9988bb;
        }
        .category-stats {
            text-align: right;
            color: #9988bb;
            font-size: 14px;
        }
        .category-stats div { margin-bottom: 5px; }
        .visit-btn {
            background: #7c52d6;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
            transition: all 0.3s;
        }
        .visit-btn:hover {
            background: #946aff;
            transform: translateY(-2px);
        }
        footer {
            text-align: center;
            padding: 25px;
            color: #9988bb;
            font-size: 14px;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .top-title h1 { font-size: 36px; }
            .stats-bar { flex-direction: column; gap: 15px; }
            .category-item { flex-direction: column; text-align: center; gap: 15px; }
            .category-stats { text-align: center; }
        }
    </style>
</head>
<body>
    <div class="top-title">
        <h1>小魔头Galgame</h1>
        <p>全站免费资源 · 无积分直接下载</p>
    </div>

    <div class="nav">
        <a href="index.php">首页</a>
        <a href="#">动漫专区</a>
        <a href="#">游戏资源</a>
        <a href="#">文件下载</a>
    </div>

    <?php if (isLoggedIn()): ?>
    <div class="user-bar">
        <span class="user-info">欢迎，<?php echo sanitize($_SESSION['nickname']); ?></span>
        <?php if (isAdmin()): ?>
            <a href="admin/index.php">管理后台</a>
        <?php endif; ?>
        <a href="logout.php">退出登录</a>
    </div>
    <?php else: ?>
    <div class="user-bar">
        <a href="login.php">登录</a>
        <a href="register.php">注册</a>
    </div>
    <?php endif; ?>

    <div class="main">
        <div class="stats-bar">
            <div class="stat-item">
                <div class="num"><?php echo $stats['user_count']; ?></div>
                <div class="label">会员总数</div>
            </div>
            <div class="stat-item">
                <div class="num"><?php echo $stats['post_count']; ?></div>
                <div class="label">帖子总数</div>
            </div>
            <div class="stat-item">
                <div class="num"><?php echo $stats['comment_count']; ?></div>
                <div class="label">评论总数</div>
            </div>
        </div>

        <div class="block">
            <h2>论坛板块</h2>
            <?php foreach ($categories as $category): 
                $cate_id = $category['id'];
                $post_count_sql = "SELECT COUNT(*) FROM post WHERE cate_id = $cate_id AND status = 1";
                $post_count = $pdo->query($post_count_sql)->fetchColumn();
            ?>
            <div class="category-item">
                <div class="category-info">
                    <h3><?php echo sanitize($category['name']); ?></h3>
                    <p><?php echo sanitize($category['desc']); ?></p>
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="visit-btn">进入板块</a>
                </div>
                <div class="category-stats">
                    <div>帖子数：<?php echo $post_count; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        小魔头Galgame论坛 · 全站免费资源 · 安卓端全屏适配
    </footer>
</body>
</html>