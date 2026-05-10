<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: ../login.php");
    exit;
}

require_once '../config.php';

$tab = $_GET['tab'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve_post':
                $pdo->prepare("UPDATE post SET is_check = 1 WHERE id = ?")->execute([$_POST['id']]);
                break;
            case 'reject_post':
                $pdo->prepare("UPDATE post SET is_check = 2 WHERE id = ?")->execute([$_POST['id']]);
                break;
            case 'delete_post':
                $pdo->prepare("UPDATE post SET status = 0 WHERE id = ?")->execute([$_POST['id']]);
                break;
            case 'delete_user':
                $pdo->prepare("UPDATE user SET status = 0 WHERE id = ?")->execute([$_POST['id']]);
                break;
            case 'toggle_role':
                $new_role = $_POST['role'] == 1 ? 0 : 1;
                $pdo->prepare("UPDATE user SET role = ? WHERE id = ?")->execute([$new_role, $_POST['id']]);
                break;
            case 'delete_comment':
                $pdo->prepare("DELETE FROM comment WHERE id = ?")->execute([$_POST['id']]);
                break;
        }
        header("Location: ?tab=" . $tab);
        exit;
    }
}

$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM user WHERE status = 1) as user_count,
    (SELECT COUNT(*) FROM post WHERE status = 1) as post_count,
    (SELECT COUNT(*) FROM comment) as comment_count,
    (SELECT COUNT(*) FROM post WHERE is_check = 0) as pending_count
")->fetch();

$pending_posts = $pdo->query("SELECT p.*, u.username, u.nickname, c.name as cate_name 
                              FROM post p 
                              LEFT JOIN user u ON p.user_id = u.id 
                              LEFT JOIN category c ON p.cate_id = c.id 
                              WHERE p.is_check = 0 
                              ORDER BY p.create_time DESC LIMIT 10")->fetchAll();

$recent_posts = $pdo->query("SELECT p.*, u.username, u.nickname, c.name as cate_name 
                             FROM post p 
                             LEFT JOIN user u ON p.user_id = u.id 
                             LEFT JOIN category c ON p.cate_id = c.id 
                             WHERE p.status = 1 
                             ORDER BY p.create_time DESC LIMIT 10")->fetchAll();

$users = $pdo->query("SELECT * FROM user WHERE status = 1 ORDER BY create_time DESC LIMIT 20")->fetchAll();

$categories = $pdo->query("SELECT * FROM category ORDER BY sort ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 小魔头Galgame论坛</title>
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
        .header {
            background: linear-gradient(135deg, #4b2e83, #2c1b54);
            padding: 20px;
            border-bottom: 4px solid #c9a8ff;
        }
        .header h1 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 36px;
            color: #ffe699;
            text-align: center;
        }
        .nav {
            display: flex;
            justify-content: center;
            background: #251a3d;
            padding: 15px;
            gap: 10px;
        }
        .nav a {
            padding: 10px 20px;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            background: #3d2e66;
            transition: all 0.3s;
        }
        .nav a:hover, .nav a.active {
            background: #7c52d6;
        }
        .main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #271c44;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 1px solid #6348a0;
        }
        .stat-card .num {
            font-size: 36px;
            font-weight: bold;
            color: #ffd878;
        }
        .stat-card .label {
            color: #9988bb;
            margin-top: 8px;
        }
        .panel {
            background: #271c44;
            border-radius: 12px;
            border: 1px solid #6348a0;
            padding: 25px;
            margin-bottom: 20px;
        }
        .panel h2 {
            font-family: 'ZCOOL KuaiLe', cursive;
            font-size: 24px;
            color: #ffd878;
            margin-bottom: 20px;
            border-left: 4px solid #c9a8ff;
            padding-left: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #3d2e66;
        }
        th {
            color: #c9a8ff;
            background: #1a1423;
        }
        tr:hover { background: rgba(124, 82, 214, 0.1); }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            margin-right: 5px;
        }
        .btn-approve { background: #28a745; color: #fff; }
        .btn-reject { background: #dc3545; color: #fff; }
        .btn-delete { background: #6c757d; color: #fff; }
        .btn-moderator { background: #ffc107; color: #000; }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-approved { background: #28a745; color: #fff; }
        .badge-rejected { background: #dc3545; color: #fff; }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background: #7c52d6;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .back-link:hover { background: #946aff; }
        footer {
            text-align: center;
            padding: 25px;
            color: #9988bb;
            font-size: 14px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>管理后台</h1>
    </div>

    <div class="nav">
        <a href="?tab=dashboard" class="<?php echo $tab == 'dashboard' ? 'active' : ''; ?>">数据统计</a>
        <a href="?tab=pending" class="<?php echo $tab == 'pending' ? 'active' : ''; ?>">待审核 (<?php echo $stats['pending_count']; ?>)</a>
        <a href="?tab=posts" class="<?php echo $tab == 'posts' ? 'active' : ''; ?>">帖子管理</a>
        <a href="?tab=users" class="<?php echo $tab == 'users' ? 'active' : ''; ?>">用户管理</a>
        <a href="../index.php">返回前台</a>
    </div>

    <div class="main">
        <?php if ($tab == 'dashboard'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="num"><?php echo $stats['user_count']; ?></div>
                <div class="label">会员总数</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $stats['post_count']; ?></div>
                <div class="label">帖子总数</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $stats['comment_count']; ?></div>
                <div class="label">评论总数</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $stats['pending_count']; ?></div>
                <div class="label">待审核</div>
            </div>
        </div>

        <div class="panel">
            <h2>最新帖子</h2>
            <table>
                <tr>
                    <th>标题</th>
                    <th>板块</th>
                    <th>作者</th>
                    <th>时间</th>
                    <th>状态</th>
                </tr>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td><a href="../post.php?id=<?php echo $post['id']; ?>" style="color:#c9a8ff;"><?php echo sanitize($post['title']); ?></a></td>
                    <td><?php echo sanitize($post['cate_name']); ?></td>
                    <td><?php echo sanitize($post['nickname'] ?: $post['username']); ?></td>
                    <td><?php echo timeAgo($post['create_time']); ?></td>
                    <td>
                        <?php 
                        $status_class = $post['is_check'] == 1 ? 'approved' : ($post['is_check'] == 2 ? 'rejected' : 'pending');
                        $status_text = $post['is_check'] == 1 ? '已通过' : ($post['is_check'] == 2 ? '已拒绝' : '待审核');
                        echo "<span class='badge badge-$status_class'>$status_text</span>";
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($tab == 'pending'): ?>
        <a href="../index.php" class="back-link">← 返回前台</a>
        <div class="panel">
            <h2>待审核帖子</h2>
            <?php if (empty($pending_posts)): ?>
                <p style="text-align:center;color:#9988bb;padding:40px;">暂无待审核的帖子</p>
            <?php else: ?>
            <table>
                <tr>
                    <th>标题</th>
                    <th>板块</th>
                    <th>作者</th>
                    <th>时间</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($pending_posts as $post): ?>
                <tr>
                    <td><a href="../post.php?id=<?php echo $post['id']; ?>" style="color:#c9a8ff;"><?php echo sanitize($post['title']); ?></a></td>
                    <td><?php echo sanitize($post['cate_name']); ?></td>
                    <td><?php echo sanitize($post['nickname'] ?: $post['username']); ?></td>
                    <td><?php echo timeAgo($post['create_time']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="approve_post">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn btn-approve">通过</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="reject_post">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn btn-reject">拒绝</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($tab == 'posts'): ?>
        <div class="panel">
            <h2>所有帖子</h2>
            <table>
                <tr>
                    <th>标题</th>
                    <th>板块</th>
                    <th>作者</th>
                    <th>下载</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td><a href="../post.php?id=<?php echo $post['id']; ?>" style="color:#c9a8ff;"><?php echo sanitize($post['title']); ?></a></td>
                    <td><?php echo sanitize($post['cate_name']); ?></td>
                    <td><?php echo sanitize($post['nickname'] ?: $post['username']); ?></td>
                    <td><?php echo $post['download_count']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('确定删除?')">删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($tab == 'users'): ?>
        <div class="panel">
            <h2>用户管理</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>昵称</th>
                    <th>角色</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo sanitize($user['username']); ?></td>
                    <td><?php echo sanitize($user['nickname']); ?></td>
                    <td>
                        <?php 
                        $role_text = $user['role'] == 2 ? '管理员' : ($user['role'] == 1 ? '版主' : '普通用户');
                        echo $role_text;
                        ?>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($user['create_time'])); ?></td>
                    <td>
                        <?php if ($user['role'] != 2): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_role">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                            <button type="submit" class="btn btn-moderator">设为版主</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('确定禁用该用户?')">禁用</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        小魔头Galgame论坛 · 管理后台
    </footer>
</body>
</html>